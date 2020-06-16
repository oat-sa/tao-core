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

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\search\index\IndexUpdaterInterface;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class DeleteIndexProperty implements Action, ServiceLocatorAwareInterface, TaskAwareInterface
{
    use ServiceLocatorAwareTrait;
    use TaskAwareTrait;
    use LoggerAwareTrait;

    public function __invoke($params)
    {
        [$class, $property] = $params;

        $class = new core_kernel_classes_Class($class['uriResource']);
        $property = new core_kernel_classes_Property($property['uriResource']);
        $resources = $class->getInstances(true);

        /** @var IndexUpdaterInterface $indexUpdater */
        $indexUpdater = $this->getServiceLocator()->get(IndexUpdaterInterface::SERVICE_ID);
        $indexUpdater->deleteProperty($property->getLabel(), $resources);

        $this->getLogger()->debug('Item processed');
    }
}