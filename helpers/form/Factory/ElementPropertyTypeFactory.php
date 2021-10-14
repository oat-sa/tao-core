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

namespace oat\tao\helpers\form\Factory;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\helpers\form\Specification\WidgetChangeableSpecification;
use oat\tao\model\Specification\PropertySpecificationInterface;
use tao_helpers_form_FormElement;
use tao_helpers_form_FormFactory;
use tao_helpers_form_GenerisFormFactory;

class ElementPropertyTypeFactory
{
    /** @var PropertySpecificationInterface */
    private $primaryOrSecondaryPropertySpecification;

    /** @var WidgetChangeableSpecification */
    private $widgetChangeableSpecification;

    /** @var array */
    private $propertyMap;

    /** @var tao_helpers_form_FormElement */
    private $element;

    public function __construct(
        PropertySpecificationInterface $primaryOrSecondaryPropertySpecification,
        WidgetChangeableSpecification $widgetChangeableSpecification
    ) {
        $this->primaryOrSecondaryPropertySpecification = $primaryOrSecondaryPropertySpecification;
        $this->widgetChangeableSpecification = $widgetChangeableSpecification;
    }

    public function withPropertyMap(array $propertyMap): self
    {
        $this->propertyMap = $propertyMap;

        return $this;
    }

    public function withElement(tao_helpers_form_FormElement $element): self
    {
        $this->element = $element;

        return $this;
    }

    public function create(
        core_kernel_classes_Property $property,
        array $newData,
        int $index,
        &$checkRange
    ): ?tao_helpers_form_FormElement {
        $element = $this->createElement($index);
        $element->setDescription(__('Type'));
        $element->addAttribute('class', 'property-type property');
        $element->setEmptyOption(' --- ' . __('select') . ' --- ');

        $options = [];
        $widgetUri = $this->getWidgetUri($property, $index, $newData);

        $this->disable($property, $element);

        foreach ($this->getPropertyMap() as $typeKey => $map) {
            if (!$this->widgetChangeableSpecification->isSatisfiedBy($map['widget'], $property)) {
                continue;
            }

            $options[$typeKey] = $map['title'];

            if ($widgetUri && $widgetUri === $map['widget']) {
                $element->setValue($typeKey);
                $checkRange = is_null($map['range']);
            }
        }

        $element->setOptions($options);

        return $element;
    }

    private function createElement(int $index): tao_helpers_form_FormElement
    {
        return $this->element ?? tao_helpers_form_FormFactory::getElement("{$index}_type", 'Combobox');
    }

    private function getPropertyMap(): array
    {
        if (!$this->propertyMap) {
            $this->propertyMap = tao_helpers_form_GenerisFormFactory::getPropertyMap();
        }

        return $this->propertyMap;
    }

    private function disable(core_kernel_classes_Property $property, tao_helpers_form_FormElement $element): void
    {
        if (
            $this->primaryOrSecondaryPropertySpecification->isSatisfiedBy($property)
            && $property->getWidget() instanceof core_kernel_classes_Resource
            && SearchTextBox::WIDGET_ID === $property->getWidget()->getUri()
        ) {
            $element->disable();
        }
    }

    private function getWidgetUri(core_kernel_classes_Property $property, int $index, array $data): ?string
    {
        $selectedType = $data[$index . '_type'];
        $selectedType = $this->getPropertyMap()[$selectedType]['widget'] ?? null;

        if ($selectedType !== null) {
            return $selectedType;
        }

        if ($property->getWidget() instanceof core_kernel_classes_Resource) {
            return $property->getWidget()->getUri();
        }

        return null;
    }
}
