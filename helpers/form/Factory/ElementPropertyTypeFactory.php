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
    /**
     * @var PropertySpecificationInterface
     */
    private $primaryOrSecondaryPropertySpecification;

    /** @var WidgetChangeableSpecification */
    private $widgetChangeableSpecification;

    public function __construct(
        PropertySpecificationInterface $primaryOrSecondaryPropertySpecification,
        WidgetChangeableSpecification $widgetChangeableSpecification
    ) {
        $this->primaryOrSecondaryPropertySpecification = $primaryOrSecondaryPropertySpecification;
        $this->widgetChangeableSpecification = $widgetChangeableSpecification;
    }

    public function create(
        core_kernel_classes_Property $property,
        int $index,
        &$checkRange
    ): ?tao_helpers_form_FormElement {
        $element = tao_helpers_form_FormFactory::getElement("{$index}_type", 'Combobox');
        $element->setDescription(__('Type'));
        $element->addAttribute('class', 'property-type property');
        $element->setEmptyOption(' --- ' . __('select') . ' --- ');

        $options = [];
        $widget = $property->getWidget();

        $this->disable($property, $element);

        foreach (tao_helpers_form_GenerisFormFactory::getPropertyMap() as $typeKey => $map) {
            if (!$this->widgetChangeableSpecification->isSatisfiedBy($map['widget'], $property)) {
                continue;
            }

            $options[$typeKey] = $map['title'];

            if (!$widget instanceof core_kernel_classes_Resource) {
                continue;
            }

            if ($widget->getUri() === $map['widget']) {
                $element->setValue($typeKey);
                $checkRange = is_null($map['range']);
            }
        }

        $element->setOptions($options);

        return $element;
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
}
