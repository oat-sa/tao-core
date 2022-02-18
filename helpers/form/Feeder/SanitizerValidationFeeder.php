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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\helpers\form\Feeder;

use tao_helpers_Uri;
use RuntimeException;
use tao_helpers_form_Form;
use InvalidArgumentException;
use tao_helpers_form_Validator;
use tao_helpers_form_FormElement;
use tao_helpers_form_elements_Textbox;
use tao_helpers_form_elements_Textarea;

class SanitizerValidationFeeder implements SanitizerValidationFeederInterface
{
    private const ALLOWED_WIDGETS = [
        tao_helpers_form_elements_Textbox::WIDGET_ID,
        tao_helpers_form_elements_Textarea::WIDGET_ID,
    ];

    /** @var tao_helpers_form_Form */
    private $form;

    /** @var tao_helpers_form_Validator[] */
    private $validators = [];

    /** @var tao_helpers_form_FormElement[] */
    private $elements = [];

    public function setForm(tao_helpers_form_Form $form): SanitizerValidationFeederInterface
    {
        $this->reset();

        $this->form = $form;

        return $this;
    }

    public function addValidator(tao_helpers_form_Validator $validator): SanitizerValidationFeederInterface
    {
        $this->validators[] = $validator;

        return $this;
    }

    public function addElement(tao_helpers_form_FormElement $element): SanitizerValidationFeederInterface
    {
        $this->checkFormAvailability();

        if ($this->isElementValid($element) && $this->form->hasElement(tao_helpers_Uri::encode($element->getName()))) {
            $this->elements[] = $element;
        }

        return $this;
    }

    public function addElementByUri(string $elementUri): SanitizerValidationFeederInterface
    {
        $this->checkFormAvailability();

        $element = $this->form->getElement(tao_helpers_Uri::encode($elementUri));

        if ($element !== null && $this->isElementValid($element)) {
            $this->elements[] = $element;
        }

        return $this;
    }

    public function feed(): void
    {
        if (empty($this->validators)) {
            return;
        }

        foreach ($this->elements as $element) {
            if (empty($this->getValue($element))) {
                continue;
            }

            foreach ($this->validators as $validator) {
                $element->addValidator($validator);
            }
        }
    }

    private function getValue(tao_helpers_form_FormElement $element): ?string
    {
        return $element->getInputValue() ?? $element->getRawValue();
    }

    private function reset(): void
    {
        $this->validators = [];
        $this->elements = [];
    }

    private function checkFormAvailability(): void
    {
        if (!isset($this->form)) {
            throw new RuntimeException('Cannot add element because form is not set');
        }
    }

    private function isElementValid(tao_helpers_form_FormElement $element): bool
    {
        return in_array($element->getWidget(), self::ALLOWED_WIDGETS, true);
    }
}
