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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\index\DocumentBuilder;

use core_kernel_classes_Property;
use oat\generis\model\WidgetRdf;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use tao_helpers_form_elements_Calendar;
use tao_helpers_form_elements_Checkbox;
use tao_helpers_form_elements_Combobox;
use tao_helpers_form_elements_Htmlarea;
use tao_helpers_form_elements_Radiobox;
use tao_helpers_form_elements_Textarea;
use tao_helpers_form_elements_Textbox;
use tao_helpers_Uri;

class PropertyIndexReferenceFactory
{
    private const ALLOWED_DYNAMIC_TYPES = [
        tao_helpers_form_elements_Textbox::WIDGET_ID,
        tao_helpers_form_elements_Textarea::WIDGET_ID,
        tao_helpers_form_elements_Htmlarea::WIDGET_ID,
        tao_helpers_form_elements_Checkbox::WIDGET_ID,
        tao_helpers_form_elements_Combobox::WIDGET_ID,
        tao_helpers_form_elements_Radiobox::WIDGET_ID,
        tao_helpers_form_elements_Calendar::WIDGET_ID,
        SearchTextBox::WIDGET_ID,
        SearchDropdown::WIDGET_ID,
    ];

    public function create(core_kernel_classes_Property $property): ?string
    {
        $propertyType = $property->getOnePropertyValue($property->getProperty(WidgetRdf::PROPERTY_WIDGET));

        if (null === $propertyType) {
            return null;
        }

        $propertyTypeUri = $propertyType->getUri();

        if (!in_array($propertyTypeUri, self::ALLOWED_DYNAMIC_TYPES)) {
            return null;
        }

        $propertyTypeArray = explode('#', $propertyTypeUri, 2);
        $propertyTypeId = end($propertyTypeArray);

        if (false === $propertyTypeId) {
            return null;
        }

        return $propertyTypeId . '_' . tao_helpers_Uri::encode($property->getUri());
    }

    //@TODO FIXME Find a better name than "raw"...
    public function createRaw(core_kernel_classes_Property $property): ?string
    {
        $reference = $this->create($property);

        if (strpos($reference, 'RadioBox') === 0 ||
            strpos($reference, 'ComboBox') === 0 ||
            strpos($reference, 'Checkbox') === 0 ||
            strpos($reference, 'HTMLArea') === 0 ||
            strpos($reference, 'SearchTextBox') === 0 ||
            strpos($reference, 'SearchDropdown') === 0
        ) {
            return $reference ? ($reference . '_raw') : null;
        }

        return null;
    }
}
