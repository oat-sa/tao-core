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
 */

declare(strict_types=1);

namespace oat\tao\model\search\tasks;

use common_Exception;
use common_report_Report;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_persistence_Exception;
use oat\generis\model\OntologyAwareTrait;
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

class RenameIndexProperties implements Action, ServiceLocatorAwareInterface, TaskAwareInterface
{
    use ServiceLocatorAwareTrait;
    use TaskAwareTrait;
    use LoggerAwareTrait;

    /**
     * @param $properties
     *
     * @return common_report_Report
     * @throws common_Exception
     * @throws core_kernel_persistence_Exception
     */
    public function __invoke($properties): common_report_Report
    {
        $indexProperties = [];
        foreach ($properties as $propertyData) {
            if (!isset($propertyData['oldLabel'], $propertyData['oldPropertyType'], $propertyData['uri'])) {
                continue;
            }

            $property = new core_kernel_classes_Property($propertyData['uri']);
            $domain = $property->getDomain();
            if (null === $domain) {
                continue;
            }

            $firstDomain = $domain->get(0);
            if (null === $firstDomain) {
                continue;
            }

            $type = $firstDomain->getUri();

            /** @noinspection PhpParamsInspection */
            $parentClasses = $this->getParentClasses($firstDomain);

            /** @var core_kernel_classes_Property $propertyType */
            $propertyType = $property->getOnePropertyValue(new core_kernel_classes_Property(WidgetRdf::PROPERTY_WIDGET));
            if (null === $propertyType) {
                continue;
            }

            $indexProperties[] = [
                'type' => $type,
                'parentClasses' => $parentClasses,
                'oldName' => $this->formatField($propertyData['oldLabel'], $propertyData['oldPropertyType']),
                'newName' => $this->formatField($property->getLabel(), $propertyType->getUri())
            ];
        }

        $this->getLogger()->info('Indexing properties', $indexProperties);

        try {
            $this->getServiceLocator()->get(IndexUpdaterInterface::SERVICE_ID)->updateProperties($indexProperties);
        } catch (Throwable $exception) {
            $message = 'Failed during update search index';
            $this->getLogger()->error($message, (array)$exception);

            return common_report_Report::createFailure(__($message));
        }

        $message = 'Search index was successfully updated.';
        $this->getLogger()->info($message, $indexProperties);

        return common_report_Report::createSuccess(__($message));
    }

    protected function formatField(string $label, string $propertyTypeUri): string
    {
        $parsedUri = parse_url($propertyTypeUri);

        return ($parsedUri['fragment'] ?? '') . '_' . tao_helpers_Slug::create($label);
    }

    private function getParentClasses(core_kernel_classes_Class $domain): array
    {
        $result = [];

        foreach ($domain->getParentClasses(true) as $parentClass) {
            $result[] = $parentClass->getUri();
        }

        return $result;
    }
}
