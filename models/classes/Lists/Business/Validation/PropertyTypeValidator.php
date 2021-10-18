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

use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\helpers\form\Decorator\ElementDecorator;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\helpers\form\validators\CrossElementEvaluationAware;
use oat\tao\model\Lists\Business\Specification\PrimaryPropertySpecification;
use oat\tao\model\Lists\Business\Specification\PropertySpecificationContext;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;
use tao_helpers_form_elements_Combobox;
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

    /** @var ElementDecorator */
    private $elementDecorator;

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
        if (!$this->isPrimaryOrSecondary()) {
            return true;
        }

        if ($this->isAllowedSearchTexBoxForPrimaryOrSecondaryProperty()) {
            return true;
        }

        if ($this->isAllowedSingleChoiceForPrimaryOrSecondaryProperty()) {
            return false;
        }

        return true;
    }

    public function acknowledge(tao_helpers_form_Form $form): void
    {
        $this->elementDecorator = new ElementDecorator($this->ontology, $form, $this->element);
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

    private function isAllowedSearchTexBoxForPrimaryOrSecondaryProperty(): bool
    {
        return $this->elementDecorator->getCurrentWidgetUri() === SearchTextBox::WIDGET_ID &&
            $this->elementDecorator->getNewWidgetUri() === SearchTextBox::WIDGET_ID;
    }

    private function isAllowedSingleChoiceForPrimaryOrSecondaryProperty(): bool
    {
        if (
            in_array(
                $this->elementDecorator->getCurrentWidgetUri(),
                [
                    tao_helpers_form_elements_Combobox::WIDGET_ID,
                    SearchDropdown::WIDGET_ID
                ]
            )
        ) {
            return in_array(
                $this->elementDecorator->getNewWidgetUri(),
                [
                    tao_helpers_form_elements_Combobox::WIDGET_ID,
                    SearchDropdown::WIDGET_ID
                ]
            );
        }

        return in_array(
            $this->elementDecorator->getNewWidgetUri(),
            [
                tao_helpers_form_elements_Combobox::WIDGET_ID,
                SearchDropdown::WIDGET_ID,
                SearchTextBox::WIDGET_ID
            ]
        );
    }

    private function isPrimaryOrSecondary(): bool
    {
        $property = $this->elementDecorator->getProperty();

        return $this->primaryPropertySpecification->isSatisfiedBy($property) ||
            $this->secondaryPropertySpecification->isSatisfiedBy(
                new PropertySpecificationContext(
                    [
                        PropertySpecificationContext::PARAM_PROPERTY => $property,
                        PropertySpecificationContext::PARAM_FORM_INDEX => $this->elementDecorator->getIndex(),
                        PropertySpecificationContext::PARAM_FORM_DATA => $this->elementDecorator->getFormData()
                    ]
                )
            );
    }
}
