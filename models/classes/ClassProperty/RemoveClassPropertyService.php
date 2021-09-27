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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\ClassProperty;

use core_kernel_classes_Property;
use oat\oatbox\event\EventManager;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\Lists\DataAccess\Repository\ParentPropertyListCachedRepository;
use oat\tao\model\search\tasks\IndexTrait;
use oat\oatbox\service\ConfigurableService;
use Psr\Http\Message\ServerRequestInterface;
use oat\tao\model\search\index\OntologyIndex;
use oat\tao\model\event\ClassPropertyRemovedEvent;
use oat\generis\model\data\event\ClassPropertyDeletedEvent;

class RemoveClassPropertyService extends ConfigurableService
{
    use OntologyAwareTrait;
    use IndexTrait;

    public function remove(ServerRequestInterface $request): bool
    {
        $parsedBody = $request->getParsedBody();

        $class = $this->getClass($parsedBody['classUri']);
        $property = $this->getProperty($parsedBody['uri']);
        $propertyType = $this->getPropertyType($property);

        if ($propertyType !== null) {
            $propertyName = $this->getPropertyRealName($property->getLabel(), $propertyType->getUri());
            $this->getEventManager()->trigger(new ClassPropertyRemovedEvent($class, $propertyName));
        }

        $this->invalidatePropertyCache($property);

        // Delete property mode
        foreach ($class->getProperties() as $classProperty) {
            if ($classProperty->equals($property)) {
                $indexes = $property->getPropertyValues($this->getProperty(OntologyIndex::PROPERTY_INDEX));

                // Delete property and the existing values of this property
                if ($property->delete(true)) {
                    $this->getEventManager()->trigger(
                        new ClassPropertyDeletedEvent(
                            $class,
                            [
                                'propertyUri' => $property->getUri()
                            ]
                        )
                    );

                    // Delete index linked to the property
                    foreach ($indexes as $indexUri) {
                        $index = $this->getResource($indexUri);
                        $index->delete(true);
                    }


                    return true;
                }
            }
        }

        return false;
    }

    private function invalidatePropertyCache(core_kernel_classes_Property $property): void
    {
        if ($property->getRange()) {
            $this->getParentPropertyListCachedRepository()->deleteCache(
                [
                    'listUri' => $property->getRange()->getUri()
                ]
            );
        }
    }

    private function getEventManager(): EventManager
    {
        return $this->getServiceManager()->get(EventManager::SERVICE_ID);
    }

    private function getParentPropertyListCachedRepository(): ParentPropertyListCachedRepository
    {
        return $this->getServiceLocator()->get(ParentPropertyListCachedRepository::class);
    }
}
