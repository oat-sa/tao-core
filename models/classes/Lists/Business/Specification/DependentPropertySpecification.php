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

use core_kernel_classes_Property;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Specification\PropertySpecificationInterface;
use oat\tao\model\Lists\Business\Service\RemoteSourcedListOntology;

class DependentPropertySpecification extends ConfigurableService implements PropertySpecificationInterface
{
    public function isSatisfiedBy(core_kernel_classes_Property $property): bool
    {
        $propertyDependsOnProperty = $property->getProperty(
            RemoteSourcedListOntology::PROPERTY_DEPENDS_ON_PROPERTY
        );

        return $property->getOnePropertyValue($propertyDependsOnProperty) !== null;
    }
}
