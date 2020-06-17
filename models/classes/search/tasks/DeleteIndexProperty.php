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
use core_kernel_classes_Property;
use oat\generis\model\WidgetRdf;
use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\search\index\IndexUpdaterInterface;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use tao_helpers_Slug;
use Throwable;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class DeleteIndexProperty implements Action, ServiceLocatorAwareInterface, TaskAwareInterface
{
    use ServiceLocatorAwareTrait;
    use TaskAwareTrait;
    use LoggerAwareTrait;

    public function __invoke($params): common_report_Report
    {
        [$class, $property] = $params;

        $class = new core_kernel_classes_Class($class['uriResource']);
        $property = new core_kernel_classes_Property($property['uriResource']);

        $propertyData = [
            'name' => $this->getPropertyRealName($property),
            'type' => $class->getUri(),
            'parentClasses' => $this->extractParentClasses($class)
        ];

        try {
            $this->getServiceLocator()
                ->get(IndexUpdaterInterface::SERVICE_ID)
                ->deleteProperty($propertyData);
        } catch (Throwable $exception) {
            $message = 'Failed to remove class property from search index';
            $this->getLogger()->error($message, (array)$exception);

            return common_report_Report::createFailure(__($message));
        }

        $message = 'Class property removed successfully.';
        $this->getLogger()->info($message, $propertyData);

        return common_report_Report::createSuccess(__($message));
    }

    private function getPropertyRealName(core_kernel_classes_Property $property): string
    {
        $propertyType = $property->getOnePropertyValue(new core_kernel_classes_Property(WidgetRdf::PROPERTY_WIDGET));
        $parsedUri = parse_url($propertyType->getUri());

        return ($parsedUri['fragment'] ?? '') . '_' . tao_helpers_Slug::create($property->getLabel());
    }

    private function extractParentClasses(core_kernel_classes_Class $class): array
    {
        return array_map(function ($currentClass) {
            return $currentClass->getUri();
        }, $class->getParentClasses(true));
    }
}