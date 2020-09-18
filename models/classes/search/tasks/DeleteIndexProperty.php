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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\search\tasks;

use common_report_Report;
use core_kernel_classes_Class;
use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\search\index\IndexUpdaterInterface;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Throwable;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class DeleteIndexProperty implements Action, ServiceLocatorAwareInterface, TaskAwareInterface
{
    use ServiceLocatorAwareTrait;
    use TaskAwareTrait;
    use LoggerAwareTrait;
    use IndexTrait;

    public function __invoke($params): common_report_Report
    {
        [$class, $propertyName] = $params;

        $class = new core_kernel_classes_Class($class['uriResource']);
        $propertyData = [
            'name' => $propertyName,
            'type' => $class->getUri(),
            'parentClasses' => $this->getParentClasses($class)
        ];

        $this->logInfo('Removing property from index');

        try {
            $this->getServiceLocator()
                ->get(IndexUpdaterInterface::SERVICE_ID)
                ->deleteProperty($propertyData);
        } catch (Throwable $exception) {
            $message = 'Failed to remove class property from search index';
            $this->logError($message);

            return common_report_Report::createFailure(__($message));
        }

        $message = 'Class property removed successfully.';
        $this->logInfo($message);

        return common_report_Report::createSuccess(__($message));
    }
}
