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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\tasks;

use common_Exception;
use common_report_Report;
use core_kernel_classes_Property;
use core_kernel_persistence_Exception;
use oat\oatbox\action\Action;
use oat\oatbox\reporting\ReportInterface;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Throwable;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class RenameIndexProperties
    extends AbstractSearchTask
    implements Action, TaskAwareInterface
{
    use TaskAwareTrait;
    use IndexTrait;

    /**
     * @param $properties
     *
     * @return common_report_Report
     * @throws common_Exception
     * @throws core_kernel_persistence_Exception
     */
    public function __invoke($properties): ReportInterface
    {
        $indexProperties = [];
        foreach ($properties as $propertyData) {
            if (
                !isset(
                    $propertyData['oldLabel'],
                    $propertyData['oldAlias'],
                    $propertyData['oldPropertyType'],
                    $propertyData['uri']
                )
            ) {
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
            $propertyType = $this->getPropertyType($property);
            if (null === $propertyType) {
                continue;
            }

            $indexProperties[] = [
                'type' => $type,
                'parentClasses' => $parentClasses,
                'oldName' => $this->getPropertyRealName($propertyData['oldLabel'], $propertyData['oldPropertyType']),
                'newName' => $this->getPropertyRealName($property->getLabel(), $propertyType->getUri())
            ];
        }

        try {
            $this->logInfo('Indexing properties');
            $this->getIndexUpdater()->updatePropertiesName($indexProperties);
        } catch (Throwable $exception) {
            $message = 'Failed during update search index';
            $this->logError($message);

            return $this->buildErrorReport(__($message));
        }

        $message = 'Search index was successfully updated.';
        $this->logInfo($message);

        return $this->buildSuccessReport(__($message));
    }
}
