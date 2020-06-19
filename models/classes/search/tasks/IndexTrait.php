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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\search\tasks;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\WidgetRdf;
use tao_helpers_Slug;

trait IndexTrait
{
    public function getPropertyRealName(string $label, string $propertyTypeUri): string
    {
        $parsedUri = parse_url($propertyTypeUri);

        return ($parsedUri['fragment'] ?? '') . '_' . tao_helpers_Slug::create($label);
    }

    public function getPropertyType(core_kernel_classes_Property $property): ?core_kernel_classes_Resource
    {
        $widget = new core_kernel_classes_Property(WidgetRdf::PROPERTY_WIDGET);

        return $property->getOnePropertyValue($widget);
    }

    public function getParentClasses(core_kernel_classes_Class $class): array
    {
        $result = [];

        foreach ($class->getParentClasses(true) as $parentClass) {
            $result[] = $parentClass->getUri();
        }

        return $result;
    }
}
