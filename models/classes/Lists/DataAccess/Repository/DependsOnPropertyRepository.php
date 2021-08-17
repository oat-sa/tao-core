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
use oat\tao\model\TaoOntology;
use core_kernel_classes_Property;
use tao_helpers_form_GenerisFormFactory;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Lists\Business\Domain\DependsOnProperty;
use oat\tao\model\Lists\Business\Service\RemoteSourcedListOntology;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertyCollection;

class DependsOnPropertyRepository extends ConfigurableService
{
    use OntologyAwareTrait;

    /** @var core_kernel_classes_Property[] */
    private $properties;

    /** @var core_kernel_classes_Class|null[] */
    private $ranges = [];

    /** @var core_kernel_classes_Class */
    private $listsClass;

    /** @var core_kernel_classes_Property */
    private $listTypeProperty;

    /** @var core_kernel_classes_Property */
    private $dependsOnProperty;

    public function __construct($options = [])
    {
        parent::__construct($options);

        $this->listsClass = $this->getClass(TaoOntology::CLASS_URI_LIST);
        $this->listTypeProperty = $this->getProperty(RemoteSourcedListOntology::PROPERTY_LIST_TYPE);
        $this->dependsOnProperty = $this->getProperty(RemoteSourcedListOntology::PROPERTY_DEPENDS_ON_PROPERTY);
    }

    public function withProperties(array $properties)
    {
        $this->properties = $properties;
    }

    public function findAll(array $options): DependsOnPropertyCollection
    {
        $collection = new DependsOnPropertyCollection();

        /** @var core_kernel_classes_Property $property */
        $property = $options['property'];

        if (!$property->getDomain()->count() || !$this->isRemoteListProperty($property)) {
            return $collection;
        }

        /** @var core_kernel_classes_Class $class */
        $class = $property->getDomain()->get(0);

        /** @var core_kernel_classes_Property $property */
        foreach ($this->getProperties($class) as $classProperty) {
            if (
                $property->getUri() === $classProperty->getUri()
                || !$this->isRemoteListProperty($classProperty)
                || $this->isDependentProperty($classProperty)
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

    private function isRemoteListProperty(core_kernel_classes_Property $property): bool
    {
        $range = $this->getPropertyRange($property);

        if ($range === null || !$range->isSubClassOf($this->listsClass)) {
            return false;
        }

        $propertyType = $range->getOnePropertyValue($this->listTypeProperty);

        if ($propertyType === null || $propertyType->getUri() !== RemoteSourcedListOntology::LIST_TYPE_REMOTE) {
            return false;
        }

        return true;
    }

    private function isDependentProperty(core_kernel_classes_Property $property): bool
    {
        return $property->getOnePropertyValue($this->dependsOnProperty) !== null;
    }

    private function getPropertyRange(core_kernel_classes_Property $property): ?core_kernel_classes_Class
    {
        $propertyUri = $property->getUri();

        if (!isset($this->ranges[$propertyUri])) {
            $this->ranges[$propertyUri] = $property->getRange();
        }

        return $this->ranges[$propertyUri];
    }
}
