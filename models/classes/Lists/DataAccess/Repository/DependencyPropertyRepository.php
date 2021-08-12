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
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Lists\Business\Domain\DependencyProperty;
use oat\tao\model\Lists\Business\Domain\DependencyPropertyCollection;
use tao_helpers_form_GenerisFormFactory;

class DependencyPropertyRepository extends ConfigurableService
{
    public function findByProperty(core_kernel_classes_Property $property): DependencyPropertyCollection
    {
        /** @var core_kernel_classes_Class $class */
        $class = $property->getDomain()->get(0);

        $properties = tao_helpers_form_GenerisFormFactory::getClassProperties($class);
        $collection = new DependencyPropertyCollection();

        /** @var core_kernel_classes_Property $property */
        foreach ($properties as $prop) {
            // @TODO Show only properties, which relates to remote list
            // @TODO Do not show properties that already depend on the primary property - a secondary prop cannot have another secondary prop.
            if ($property->getUri() === $prop->getUri()) {
                continue;
            }

            $collection->append(new DependencyProperty($prop));
        }

        return $collection;
    }
}
