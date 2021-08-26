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

namespace oat\tao\model\Lists\Business\Specification;

use core_kernel_classes_Class;
use oat\tao\model\TaoOntology;
use core_kernel_classes_Property;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Specification\PropertySpecificationInterface;
use oat\tao\model\Lists\Business\Service\RemoteSourcedListOntology;

class RemoteListPropertySpecification extends ConfigurableService implements PropertySpecificationInterface
{
    /** @var core_kernel_classes_Class[]|null[] */
    private $ranges = [];

    public function isSatisfiedBy(core_kernel_classes_Property $property): bool
    {
        $range = $this->getPropertyRange($property);

        if ($range === null || !$range->isSubClassOf($range->getClass(TaoOntology::CLASS_URI_LIST))) {
            return false;
        }

        $propertyType = $range->getOnePropertyValue(
            $range->getProperty(RemoteSourcedListOntology::PROPERTY_LIST_TYPE)
        );

        if (
            $propertyType === null
            || $propertyType->getUri() !== RemoteSourcedListOntology::LIST_TYPE_REMOTE
        ) {
            return false;
        }

        return true;
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
