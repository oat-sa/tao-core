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
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Specification\PropertySpecificationContext;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;
use oat\tao\model\Specification\PropertySpecificationInterface;
use tao_helpers_form_elements_xhtml_Combobox;

class ElementPropertyEmptyListValuesFactory extends AbstractElementPropertyListValuesFactory
{
    private const ATTRIBUTE_FORCE_DISABLED = 'data-force-disabled';
    private const ATTRIBUTE_DISABLED_MESSAGE = 'data-disabled-message';

    /** @var PropertySpecificationInterface */
    private $primaryPropertySpecification;

    /** @var SecondaryPropertySpecification */
    private $secondaryPropertySpecification;

    /** @var FeatureFlagCheckerInterface */
    private $featureFlagChecker;

    public function __construct(
        PropertySpecificationInterface $primaryPropertySpecification,
        SecondaryPropertySpecification $secondaryPropertySpecification,
        FeatureFlagCheckerInterface $featureFlagChecker
    ) {
        $this->primaryPropertySpecification = $primaryPropertySpecification;
        $this->secondaryPropertySpecification = $secondaryPropertySpecification;
        $this->featureFlagChecker = $featureFlagChecker;
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
            !$this->featureFlagChecker->isEnabled(FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED)
        ) {
            return $element;
        }

        $isSecondaryProperty = $this->secondaryPropertySpecification->isSatisfiedBy(
            new PropertySpecificationContext(
                [
                    PropertySpecificationContext::PARAM_PROPERTY => $property,
                    PropertySpecificationContext::PARAM_FORM_INDEX => $index,
                    PropertySpecificationContext::PARAM_FORM_DATA => $newData
                ]
            )
        );

        if ($isSecondaryProperty || $this->primaryPropertySpecification->isSatisfiedBy($property)) {
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
}
