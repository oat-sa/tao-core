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
use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\SearchService;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use oat\generis\model\OntologyAwareTrait;

/**
 * Class AddSearchIndexFromArray
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 * @package oat\tao\model\search\tasks
 */
class AddSearchIndexFromArray
    extends AbstractSearchTask
    implements Action, TaskAwareInterface
{
    use OntologyAwareTrait;
    use TaskAwareTrait;

    /**
     * @param $params
     * @return \common_report_Report
     * @throws \common_exception_Error
     * @throws \common_exception_MissingParameter
     */
    public function __invoke($params)
    {
        if (count($params) < 2) {
            throw new \common_exception_MissingParameter();
        }

        list($id, $body) = $params;

        $report = $this->buildSuccessReport(
            __('Adding search index for %s', $id)
        );

        try {
            $document = $this->getIndexService()->createDocumentFromArray([
                'id' => $id,
                'body' => $body
            ]);

            $this->getSearchService()->index([$document]);
        } catch (\Exception $e) {
            $report->add(
                $this->buildErrorReport(
                    __('Error adding search index for %s with message %s', $id, $e->getMessage())
                )
            );
        }

        return $report;
    }
}
