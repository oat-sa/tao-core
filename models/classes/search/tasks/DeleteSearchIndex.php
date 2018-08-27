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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\tao\model\search\tasks;

use oat\oatbox\action\Action;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\search\Search;
/**
 * Class DeleteSearchIndex
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 * @package oat\tao\model\search\tasks
 */
class DeleteSearchIndex implements Action,ServiceLocatorAwareInterface, TaskAwareInterface
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
        if (count($params) != 1) {
            throw new \common_exception_MissingParameter();
        }
        $resourceId = array_shift($params);
        $report = new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Deleting search index for %s', $resourceId));
        $subReport = $this->deleteIndex($resourceId);
        $report->add($subReport);
        return $report;
    }
    /**
     * @param $resourceId
     * @return \common_report_Report
     */
    protected function deleteIndex($resourceId)
    {
        try {
            $this->getServiceLocator()->get(Search::SERVICE_ID)->remove($resourceId);
            $report = new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Index has been deleted for %s', $resourceId));
        } catch (\Exception $e) {
            $report = new \common_report_Report(\common_report_Report::TYPE_ERROR, __('Failed to delete index for %s', $resourceId));
        }
        return $report;
    }
}