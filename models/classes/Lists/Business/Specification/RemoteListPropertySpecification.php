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
use oat\tao\model\Specification\ClassSpecificationInterface;
use core_kernel_classes_Property;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Specification\PropertySpecificationInterface;

class RemoteListPropertySpecification extends ConfigurableService implements PropertySpecificationInterface
{
    /** @var core_kernel_classes_Class[]|null[] */
    private $ranges = [];

    public function isSatisfiedBy(core_kernel_classes_Property $property): bool
    {
        $class = $this->getPropertyRange($property);

        if ($class === null) {
            return false;
        }

        return $this->getRemoteListClassSpecification()->isSatisfiedBy($class);
    }

    private function getPropertyRange(core_kernel_classes_Property $property): ?core_kernel_classes_Class
    {
        $propertyUri = $property->getUri();

        if (!isset($this->ranges[$propertyUri])) {
            $this->ranges[$propertyUri] = $property->getRange();
        }

        return $this->ranges[$propertyUri];
    }

    private function getRemoteListClassSpecification(): ClassSpecificationInterface
    {
        return $this->getServiceManager()->getContainer()->get(RemoteListClassSpecification::class);
    }
}
