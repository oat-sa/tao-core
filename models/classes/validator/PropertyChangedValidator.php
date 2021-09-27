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

namespace oat\tao\model\validator;

use oat\generis\model\WidgetRdf;
use core_kernel_classes_Property;
use oat\tao\model\dto\OldProperty;
use oat\oatbox\service\ConfigurableService;
use oat\tao\helpers\form\ValidationRuleRegistry;

class PropertyChangedValidator extends ConfigurableService
{
    public function isPropertyChanged(core_kernel_classes_Property $property, OldProperty $oldProperty): bool
    {
        return $property->getLabel() !== $oldProperty->getLabel()
            || $this->isPropertyTypeChanged($property, $oldProperty)
            || $this->isRangeChanged($property, $oldProperty)
            || $this->isValidationRulesChanged($property, $oldProperty)
            || $this->isDependsOnPropertyCollectionChanged($property, $oldProperty);
    }

    public function isPropertyTypeChanged(core_kernel_classes_Property $property, OldProperty $oldProperty): bool
    {
        $currentPropertyType = $property->getOnePropertyValue(
            $property->getProperty(WidgetRdf::PROPERTY_WIDGET)
        );
        $oldPropertyType = $oldProperty->getPropertyType();

        if ($currentPropertyType === null && $oldPropertyType === null) {
            return false;
        }

        if (
            ($currentPropertyType !== null && $oldPropertyType === null)
            || ($currentPropertyType === null && $oldPropertyType !== null)
        ) {
            return true;
        }

        return $currentPropertyType->getUri() !== $oldPropertyType->getUri();
    }

    public function isRangeChanged(core_kernel_classes_Property $property, OldProperty $oldProperty): bool
    {
        $propertyRange = $property->getRange();
        $propertyRangeUri = $propertyRange ? $propertyRange->getUri() : null;

        return $propertyRangeUri !== $oldProperty->getRangeUri();
    }

    public function isValidationRulesChanged(core_kernel_classes_Property $property, OldProperty $oldProperty): bool
    {
        $propertyValidationRules = $property->getPropertyValues(
            $property->getProperty(ValidationRuleRegistry::PROPERTY_VALIDATION_RULE)
        );
        $oldPropertyValidationRules = $oldProperty->getValidationRules();

        return !$this->areArraysEqual($propertyValidationRules, $oldPropertyValidationRules);
    }

    public function isDependsOnPropertyCollectionChanged(
        core_kernel_classes_Property $property,
        OldProperty $oldProperty
    ): bool {
        return !$property->getDependsOnPropertyCollection()->isEqual(
            $oldProperty->getDependsOnPropertyCollection()
        );
    }

    private function areArraysEqual(array $array1, array $array2): bool
    {
        return empty(array_diff($array1, $array2)) && empty(array_diff($array2, $array1));
    }
}
