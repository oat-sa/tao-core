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

namespace oat\tao\model\Lists\Business\Validation;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\helpers\form\validators\CrossElementEvaluationAware;
use oat\tao\model\Lists\Business\Specification\PrimaryPropertySpecification;
use oat\tao\model\Lists\Business\Specification\PropertySpecificationContext;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;
use tao_helpers_form_Form;
use tao_helpers_form_FormElement;

class PropertyTypeValidator implements ValidatorInterface, CrossElementEvaluationAware
{
    /** @var Ontology */
    private $ontology;

    /** @var PrimaryPropertySpecification */
    private $primaryPropertySpecification;

    /** @var SecondaryPropertySpecification */
    private $secondaryPropertySpecification;

    /** @var tao_helpers_form_FormElement */
    private $element;

    /** @var array */
    private $options;

    /** @var tao_helpers_form_Form */
    private $form;

    public function __construct(
        Ontology $ontology,
        PrimaryPropertySpecification $primaryPropertySpecification,
        SecondaryPropertySpecification $secondaryPropertySpecification
    ) {
        $this->ontology = $ontology;
        $this->primaryPropertySpecification = $primaryPropertySpecification;
        $this->secondaryPropertySpecification = $secondaryPropertySpecification;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return self::class;
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return __('Invalid value');
    }

    /**
     * @inheritdoc
     */
    public function setMessage($message)
    {
        throw new InvalidArgumentException(
            sprintf(
                'Message for validator %s cannot be set.',
                self::class
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function evaluate($values)
    {
        $index = $this->getElementIndex();
        $property = $this->getProperty($index);

        if ($property === null) {
            return true;
        }

        if ($this->isNotPrimaryOrSecondary($property, $index)) {
            return true;
        }

        $previousWidgetUri = $property->getWidget() instanceof core_kernel_classes_Resource
            ? $property->getWidget()->getUri()
            : null;

        if ($previousWidgetUri === SearchTextBox::WIDGET_ID && $values === 'multisearchlist') {
            return true;
        }

        if (is_string($values) && !in_array($values, ['singlesearchlist', 'longlist'])) {
            return false;
        }

        return true;
    }

    public function acknowledge(tao_helpers_form_Form $form): void
    {
        $this->form = $form;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function setElement(tao_helpers_form_FormElement $element): void
    {
        $this->element = $element;
    }

    private function isNotPrimaryOrSecondary(core_kernel_classes_Property $property, int $index): bool
    {
        return !$this->primaryPropertySpecification->isSatisfiedBy($property) &&
            !$this->secondaryPropertySpecification->isSatisfiedBy(
                new PropertySpecificationContext(
                    [
                        PropertySpecificationContext::PARAM_PROPERTY => $property,
                        PropertySpecificationContext::PARAM_FORM_INDEX => $index,
                        PropertySpecificationContext::PARAM_FORM_DATA => $this->form->getValues()
                    ]
                )
            );
    }

    private function getElementIndex(): int
    {
        return (int)(explode('_', $this->element->getName())[0] ?? 0);
    }

    private function getProperty(int $index): ?core_kernel_classes_Property
    {
        $propertyUri = $newData[$index . '_uri'] ?? null;

        return $propertyUri === null
            ? null
            : $this->ontology->getProperty($propertyUri);
    }
}
