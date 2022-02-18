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
 * Copyright (c) 2018-2022 (original work) Open Assessment Technologies SA;
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

/**
 * Class AddSearchIndexFromResource
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 * @package oat\tao\model\search\tasks
 */
class AddSearchIndexFromResource
    extends AbstractSearchTask
    implements Action, TaskAwareInterface
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
    public function __invoke($params)
    {
        if (count($params) < 1) {
            throw new \common_exception_MissingParameter();
        }

        $resource = $this->getResource(array_shift($params));

        $report = $this->buildSuccessReport(
            __('Adding search index for %s', $resource->getUri())
        );

        try {
            $document = $this->getDocumentBuilder()->createDocumentFromResource(
                $resource
            );
            $this->getLegacySearchService()->index([$document]);
        } catch (\Exception $e) {
            $report->add(
                $this->buildErrorReport(
                    __('Error adding search index for %s with message %s',
                       $resource->getUri(), $e->getMessage())
                )
            );
        }

        return $report;
    }
}
