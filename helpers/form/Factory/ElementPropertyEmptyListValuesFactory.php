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
use oat\tao\model\Context\ContextInterface;
use oat\tao\model\Specification\PropertySpecificationInterface;
use tao_helpers_form_elements_xhtml_Combobox;

class ElementPropertyEmptyListValuesFactory extends AbstractElementPropertyListValuesFactory
{
    private const ATTRIBUTE_FORCE_DISABLED = 'data-force-disabled';
    private const ATTRIBUTE_DISABLED_MESSAGE = 'data-disabled-message';

    /** @var PropertySpecificationInterface */
    private $primaryPropertySpecification;

    /** @var PropertySpecificationInterface */
    private $dependentPropertySpecification;

    public function __construct(
        PropertySpecificationInterface $primaryPropertySpecification,
        PropertySpecificationInterface $dependentPropertySpecification
    ) {
        $this->primaryPropertySpecification = $primaryPropertySpecification;
        $this->dependentPropertySpecification = $dependentPropertySpecification;
    }

    public function create(ContextInterface $context): tao_helpers_form_elements_xhtml_Combobox
    {
        /** @var int $index */
        $index = $context->getParameter(ElementFactoryContext::PARAM_INDEX);

        /** @var core_kernel_classes_Property $property */
        $property = $context->getParameter(ElementFactoryContext::PARAM_PROPERTY);

        /** @var array $newData */
        $newData = $context->getParameter(ElementFactoryContext::PARAM_DATA);

        $element = $this->createElement($index, 'range');
        $element->setDescription(__('List values'));
        $element->addAttribute('class', 'property-listvalues property');
        $element->setEmptyOption(' --- ' . __('select') . ' --- ');
        $element->addAttribute(self::PROPERTY_LIST_ATTRIBUTE, true);

        if (
            $this->isSecondaryProperty($property, $newData, $index) ||
            $this->primaryPropertySpecification->isSatisfiedBy($property)
        ) {
            $element->disable();
            $element->addAttribute(
                self::ATTRIBUTE_FORCE_DISABLED,
                'true'
            );
            $element->addAttribute(
                self::ATTRIBUTE_DISABLED_MESSAGE,
                __('The field "List" is disabled because the property is part of a dependency')
            );
        }

        return $element;
    }

    private function isSecondaryProperty(core_kernel_classes_Property $property, array $newData, int $index): bool
    {
        $dependsOnProperty = $newData[$index . '_depends-on-property'] ?? null;

        if ($dependsOnProperty === null) {
            return $this->dependentPropertySpecification->isSatisfiedBy($property);
        }

        return !empty(trim($dependsOnProperty));
    }
}
