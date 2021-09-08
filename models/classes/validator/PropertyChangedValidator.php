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

use core_kernel_classes_Property;
use oat\generis\model\WidgetRdf;
use oat\tao\model\dto\OldProperty;

class PropertyChangedValidator
{
    public function isPropertyChanged(core_kernel_classes_Property $property, OldProperty $oldProperty): bool
    {
        if ((string)$property->getLabel() !== $oldProperty->getLabel()) {
            return true;
        }

        $this->isPropertyTypeChanged($property, $oldProperty);
        
        $this->isRangeChanged($property, $oldProperty);

        return false;
    }
    public function isRangeChanged(core_kernel_classes_Property $property, OldProperty $oldProperty): bool
    {
        $propertyRange = $property->getRange() ? (string)$property->getRange()->getUri() : null;
        if ($propertyRange !== $oldProperty->getrangeUri()) {
            return true;
        }
        return false;
    }
    public function isPropertyTypeChanged(core_kernel_classes_Property $property, OldProperty $oldProperty): bool
    {
        $currentPropertyType = $property
            ->getOnePropertyValue(new core_kernel_classes_Property(WidgetRdf::PROPERTY_WIDGET));
        $oldPropertyType = $oldProperty->getPropertyType();

        if (null === $currentPropertyType && null === $oldPropertyType) {
            return false;
        }

        if (
            null !== $currentPropertyType && null === $oldPropertyType
            || null === $currentPropertyType && null !== $oldPropertyType
        ) {
            return true;
        }

        $currentPropertyTypeUri = $currentPropertyType->getUri();

        if ($currentPropertyTypeUri !== $oldPropertyType->getUri()) {
            return true;
        }

        return false;
    }
}
