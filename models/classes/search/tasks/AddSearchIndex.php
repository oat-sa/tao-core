<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\tao\model\search\tasks;

use oat\oatbox\action\Action;
use oat\tao\model\search\dataProviders\DataProvider;
use oat\tao\model\search\dataProviders\OntologyDataProvider;
use oat\tao\model\search\dataProviders\SearchDataProvider;
use oat\tao\model\search\SearchService;
use oat\taoTaskQueue\model\Task\TaskAwareInterface;
use oat\taoTaskQueue\model\Task\TaskAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use oat\generis\model\OntologyAwareTrait;

/**
 * Class AddSearchIndex
 * @package oat\tao\model\search\tasks
 */
class AddSearchIndex implements Action,ServiceLocatorAwareInterface, TaskAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;
    use TaskAwareTrait;

    /**
     * @param $params
     * @return \common_report_Report
     * @throws \common_exception_Error
     * @throws \common_exception_MissingParameter
     */
    public function __invoke($params) {

        if (count($params) < 2) {
            throw new \common_exception_MissingParameter();
        }
        $id = array_shift($params);
        $dataProviderServiceId = array_shift($params);
        $customBody = array_shift($params);

        $report = new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Adding search index for %s', $id));
        $subReport = $this->addIndex($id, $dataProviderServiceId, $customBody);
        $report->add($subReport);
        return $report;
    }


    protected function addIndex($id, $dataProviderServiceId, $customBody)
    {
        try {
            /** @var DataProvider $dataProvider */
            $dataProvider = $this->getServiceLocator()->get($dataProviderServiceId);

            if ($dataProvider) {
                $dataProvider->addIndex($id, $customBody);
            }
            $report = new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Index has been added for %s', $id));
        } catch (\Exception $e) {
            $report = new \common_report_Report(\common_report_Report::TYPE_ERROR, __('Failed to add index for %s', $id));
        }

        return $report;
    }
}
