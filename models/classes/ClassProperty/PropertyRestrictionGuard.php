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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\ClassProperty;

use core_kernel_classes_Resource as Resource;
use core_kernel_classes_Property as Property;
use oat\generis\model\data\Ontology;

class PropertyRestrictionGuard
{
    private Ontology $ontology;

    public function __construct(Ontology $ontology)
    {
        $this->ontology = $ontology;
    }

    public function isPropertyRestricted(Resource $instance, Property $property, array $restrictedProperties): bool
    {
        if (!in_array($property->getUri(), array_keys($restrictedProperties))) {
            return false;
        }

        foreach ($restrictedProperties as $restrictions) {
            foreach ($restrictions as $restriction => $values) {
                $propertyRestriction = $this->ontology->getProperty($restriction);
                $propertyRestrictionValue = $instance->getOnePropertyValue($propertyRestriction);
                if ($propertyRestrictionValue && in_array((string) $propertyRestrictionValue, $values)) {
                    return false;
                }
            }
        }

        return true;
    }
}
