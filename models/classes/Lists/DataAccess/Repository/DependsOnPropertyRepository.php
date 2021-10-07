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

namespace oat\tao\model\Lists\DataAccess\Repository;

use InvalidArgumentException;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use tao_helpers_form_GenerisFormFactory;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Lists\Business\Domain\DependsOnProperty;
use oat\tao\model\Specification\PropertySpecificationInterface;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertyCollection;
use oat\tao\model\Lists\Business\Specification\DependentPropertySpecification;
use oat\tao\model\Lists\Business\Contract\DependsOnPropertyRepositoryInterface;
use oat\tao\model\Lists\Business\Specification\RemoteListPropertySpecification;
use oat\tao\model\Lists\Business\Contract\ParentPropertyListRepositoryInterface;

class DependsOnPropertyRepository extends ConfigurableService implements DependsOnPropertyRepositoryInterface
{
    /** @var core_kernel_classes_Property[] */
    private $properties;

    /** @var PropertySpecificationInterface */
    private $remoteListPropertySpecification;

    /** @var PropertySpecificationInterface */
    private $dependentPropertySpecification;

    public function withProperties(array $properties)
    {
        $this->properties = $properties;
    }

    public function findAll(array $options): DependsOnPropertyCollection
    {
        $collection = new DependsOnPropertyCollection();

        if (empty($options['property']) && empty($options['class'])) {
            throw new InvalidArgumentException('class or property filter need to be provided');
        }

        /** @var core_kernel_classes_Property $property */
        $property = $options['property'] ?? null;

        /** @var core_kernel_classes_Class $class */
        $class = $property
            ? ($property->getDomain()->count() > 0 ? $property->getDomain()->get(0) : null)
            : $options['class'] ?? null;

        if ($class === null || (empty($options['listUri']) && $property && !$property->getRange())) {
            return $collection;
        }

        if ($options['listUri'] && $property && !$property->getRange()) {
            $property = null;
        }

        $listUri = $this->getListUri($options, $property);

        if (!$listUri) {
            return $collection;
        }

        if ($property && !$this->isRemoteListProperty($property)) {
            return $collection;
        }

        $parentPropertiesUris = $this->getParentPropertyListUrisRepository()->findAllUris(
            [
                'listUri' => $listUri
            ]
        );

        if (empty($parentPropertiesUris)) {
            return $collection;
        }

        /** @var core_kernel_classes_Property $property */
        foreach ($this->getProperties($class) as $classProperty) {
            if ($property && $this->isPropertyNotSupported($property, $classProperty)) {
                continue;
            }

            if ($this->isParentProperty($classProperty, $parentPropertiesUris)) {
                $collection->append(new DependsOnProperty($classProperty));

                continue;
            }

            if ($property && $this->isSameParentProperty($property, $classProperty)) {
                return $collection;
            }
        }

        return $collection;
    }

    private function getListUri(array $options, core_kernel_classes_Property $property = null): ?string
    {
        if (empty($options['listUri']) && $property && !$property->getRange()) {
            return null;
        }

        return empty($options['listUri']) && $property
            ? $property->getRange()->getUri()
            : ($options['listUri'] ?? null);
    }

    private function isSameParentProperty(
        core_kernel_classes_Property $property,
        core_kernel_classes_Property $classProperty
    ): bool {
        $parentProperty = $classProperty->getDependsOnPropertyCollection()->current();

        return $parentProperty && $property->getUri() === $parentProperty->getUri();
    }

    private function isRemoteListProperty(core_kernel_classes_Property $property): bool
    {
        return $property->getDomain()->count() && $this->getRemoteListPropertySpecification()->isSatisfiedBy($property);
    }

    private function isParentProperty(core_kernel_classes_Property $classProperty, array $parentPropertiesUris): bool
    {
        return !$this->getDependentPropertySpecification()->isSatisfiedBy($classProperty)
            && in_array($classProperty->getUri(), $parentPropertiesUris, true);
    }

    private function isPropertyNotSupported(
        core_kernel_classes_Property $property,
        core_kernel_classes_Property $classProperty
    ): bool {
        return $property->getUri() === $classProperty->getUri()
            || !$this->getRemoteListPropertySpecification()->isSatisfiedBy($classProperty);
    }

    private function getProperties(core_kernel_classes_Class $class): array
    {
        return $this->properties ?? tao_helpers_form_GenerisFormFactory::getClassProperties($class);
    }

    private function getRemoteListPropertySpecification(): PropertySpecificationInterface
    {
        if (!isset($this->remoteListPropertySpecification)) {
            $this->remoteListPropertySpecification = $this->getServiceLocator()->get(
                RemoteListPropertySpecification::class
            );
        }

        return $this->remoteListPropertySpecification;
    }

    private function getDependentPropertySpecification(): PropertySpecificationInterface
    {
        if (!isset($this->dependentPropertySpecification)) {
            $this->dependentPropertySpecification = $this->getServiceLocator()->getContainer()->get(
                DependentPropertySpecification::class
            );
        }

        return $this->dependentPropertySpecification;
    }

    private function getParentPropertyListUrisRepository(): ParentPropertyListRepositoryInterface
    {
        return $this->getServiceLocator()->get(ParentPropertyListCachedRepository::class);
    }
}
