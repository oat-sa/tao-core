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
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Specification\ClassSpecificationInterface;
use oat\tao\model\TaoOntology;
use oat\tao\model\Lists\Business\Service\RemoteSourcedListOntology;

class RemoteListClassSpecification extends ConfigurableService implements ClassSpecificationInterface
{
    public function isSatisfiedBy(core_kernel_classes_Class $class): bool
    {
        if (!$class->isSubClassOf($class->getClass(TaoOntology::CLASS_URI_LIST))) {
            return false;
        }

        $propertyType = $class->getOnePropertyValue(
            $class->getProperty(RemoteSourcedListOntology::PROPERTY_LIST_TYPE)
        );

        return $propertyType !== null && $propertyType->getUri() === RemoteSourcedListOntology::LIST_TYPE_REMOTE;
    }
}
