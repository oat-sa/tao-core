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

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use tao_helpers_form_GenerisFormFactory;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Lists\Business\Domain\DependsOnProperty;
use oat\tao\model\Specification\PropertySpecificationInterface;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertyCollection;
use oat\tao\model\Lists\Business\Specification\DependentPropertySpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListPropertySpecification;

class DependsOnPropertyRepository extends ConfigurableService
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

        /** @var core_kernel_classes_Property $property */
        $property = $options['property'];

        if (
            !$property->getDomain()->count()
            || !$this->getRemoteListPropertySpecification()->isSatisfiedBy($property)
        ) {
            return $collection;
        }

        /** @var core_kernel_classes_Class $class */
        $class = $property->getDomain()->get(0);

        /** @var core_kernel_classes_Property $property */
        foreach ($this->getProperties($class) as $classProperty) {
            if (
                $property->getUri() === $classProperty->getUri()
                || !$this->getRemoteListPropertySpecification()->isSatisfiedBy($classProperty)
                || $this->getDependentPropertySpecification()->isSatisfiedBy($classProperty)
            ) {
                continue;
            }

            $collection->append(new DependsOnProperty($classProperty));
        }

        return $collection;
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
            $this->dependentPropertySpecification = $this->getServiceLocator()->get(
                DependentPropertySpecification::class
            );
        }

        return $this->dependentPropertySpecification;
    }
}
