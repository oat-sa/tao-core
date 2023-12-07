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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\form\DataProvider;

use core_kernel_classes_Property;

interface FormDataProviderInterface
{
    public function preloadFormData(string $classUri, string $topClassUri, string $elementUri, string $language): void;

    public function getClassProperties(\core_kernel_classes_Class $class, \core_kernel_classes_Class $topClass): array;

    public function getDataToFeedProperty(core_kernel_classes_Property $property): array;

    public function getDescriptionFromTranslatedPropertyLabel(
        core_kernel_classes_Property $property,
        string $language
    ): ?string;

    public function isPropertyList(core_kernel_classes_Property $property): bool;

    public function getPropertyListElementOptions(
        core_kernel_classes_Property $property,
        ?core_kernel_classes_Property $parentProperty,
        $instance
    ): array;

    public function getPropertyNotListElementOptions(core_kernel_classes_Property $property): array;

    public function getPropertyValidators(core_kernel_classes_Property $property): array;

    public function getPropertyInstanceValues(core_kernel_classes_Property $property, $instance, $element): array;

    public function getPropertyGUIOrder(core_kernel_classes_Property $property): array;
}
