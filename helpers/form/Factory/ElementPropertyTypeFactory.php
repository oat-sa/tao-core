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
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\model\Context\ContextInterface;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Specification\PropertySpecificationContext;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;
use oat\tao\model\Specification\PropertySpecificationInterface;
use tao_helpers_form_elements_Combobox;
use tao_helpers_form_elements_xhtml_Combobox;
use tao_helpers_form_FormFactory;
use tao_helpers_form_GenerisFormFactory;

class ElementPropertyTypeFactory implements ElementFactoryInterface
{
    public const PROPERTY_TYPE_ATTRIBUTE = 'data-property-type';

    private const DEPENDENT_SINGLE_RESTRICTED_TYPES = [
        tao_helpers_form_elements_Combobox::WIDGET_ID,
        SearchDropdown::WIDGET_ID
    ];

    private const DEPENDENT_RESTRICTED_TYPES = [
        tao_helpers_form_elements_Combobox::WIDGET_ID,
        SearchDropdown::WIDGET_ID,
        SearchTextBox::WIDGET_ID
    ];

    /** @var array */
    private $propertyMap;

    /** @var tao_helpers_form_elements_xhtml_Combobox */
    private $element;

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

    public function withPropertyMap(array $propertyMap): self
    {
        $this->propertyMap = $propertyMap;

        return $this;
    }

    public function withElement(tao_helpers_form_elements_xhtml_Combobox $element): self
    {
        $this->element = $element;

        return $this;
    }

    public function create(ContextInterface $context): tao_helpers_form_elements_xhtml_Combobox
    {
        /** @var core_kernel_classes_Property $property */
        $property = $context->getParameter(ElementFactoryContext::PARAM_PROPERTY);

        /** @var array $newData */
        $newData = $context->getParameter(ElementFactoryContext::PARAM_DATA);

        /** @var int $index */
        $index = $context->getParameter(ElementFactoryContext::PARAM_INDEX);

        $options = [];
        $hasWidgetRestrictions = false;
        $selectedWidgetUri = $this->getSelectedWidgetUri($property, $index, $newData);

        $element = $this->createElement($index);
        $element->setDescription(__('Type'));
        $element->addAttribute('class', 'property-type property');
        $element->addAttribute(self::PROPERTY_TYPE_ATTRIBUTE, $selectedWidgetUri);

        $this->disable($property, $element, $newData, $index, $selectedWidgetUri);

        foreach ($this->getPropertyMap() as $typeKey => $map) {
            if (!$this->isWidgetSupported($property, $newData, $index, $map['widget'])) {
                $hasWidgetRestrictions = true;

                continue;
            }

            $options[$typeKey] = $map['title'];

            if ($selectedWidgetUri && $selectedWidgetUri === $map['widget']) {
                $element->setValue($typeKey);
            }
        }

        if (!$hasWidgetRestrictions) {
            $element->setEmptyOption(' --- ' . __('select') . ' --- ');
        }

        $element->setOptions($options);

        return $element;
    }

    private function createElement(int $index): tao_helpers_form_elements_xhtml_Combobox
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

    private function disable(
        core_kernel_classes_Property $property,
        tao_helpers_form_elements_xhtml_Combobox $element,
        array $newData,
        int $index,
        string $selectedWidgetUri = null
    ): void {
        if (
            !$this->featureFlagChecker->isEnabled(FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED)
        ) {
            return;
        }

        if (
            !$this->primaryPropertySpecification->isSatisfiedBy($property) &&
            !$this->isSecondaryProperty($property, $newData, $index)
        ) {
            return;
        }

        if (SearchTextBox::WIDGET_ID === $selectedWidgetUri) {
            $element->disable();
        }
    }

    private function getSelectedWidgetUri(core_kernel_classes_Property $property, int $index, array $data): ?string
    {
        $widgetMapKey = $data[$index . '_type'] ?? null;
        $selectedWidgetUri = $this->getPropertyMap()[$widgetMapKey]['widget'] ?? null;

        return $selectedWidgetUri === null
            ? $this->getPreviousWidgetUri($property)
            : $selectedWidgetUri;
    }

    private function getPreviousWidgetUri(core_kernel_classes_Property $property): ?string
    {
        return $property->getWidget() instanceof core_kernel_classes_Resource
            ? $property->getWidget()->getUri()
            : null;
    }

    public function isWidgetSupported(
        core_kernel_classes_Property $property,
        array $newData,
        int $index,
        string $targetWidgetUri
    ): bool {
        if (
            !$this->featureFlagChecker->isEnabled(FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED)
        ) {
            return true;
        }

        if (
            !$this->primaryPropertySpecification->isSatisfiedBy($property) &&
            !$this->isSecondaryProperty($property, $newData, $index)
        ) {
            return true;
        }

        $previewsWidget = $this->getPreviousWidgetUri($property);

        if ($previewsWidget === $targetWidgetUri) {
            return true;
        }

        if (in_array($previewsWidget, self::DEPENDENT_SINGLE_RESTRICTED_TYPES, true)) {
            return in_array($targetWidgetUri, self::DEPENDENT_SINGLE_RESTRICTED_TYPES, true);
        }

        if ($previewsWidget === SearchTextBox::WIDGET_ID) {
            return $targetWidgetUri === SearchTextBox::WIDGET_ID;
        }

        return in_array($targetWidgetUri, self::DEPENDENT_RESTRICTED_TYPES, true);
    }

    private function isSecondaryProperty(core_kernel_classes_Property $property, array $newData, int $index): bool
    {
        return $this->secondaryPropertySpecification->isSatisfiedBy(
            new PropertySpecificationContext(
                [
                    PropertySpecificationContext::PARAM_PROPERTY => $property,
                    PropertySpecificationContext::PARAM_FORM_INDEX => $index,
                    PropertySpecificationContext::PARAM_FORM_DATA => $newData
                ]
            )
        );
    }
}
