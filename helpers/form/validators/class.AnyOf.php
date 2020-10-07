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
 * Copyright (c) 2020 (original work) (update and modification) Open Assessment Technologies SA
 */

declare(strict_types=1);

use oat\tao\helpers\form\validators\CrossElementEvaluationAware;

class tao_helpers_form_validators_AnyOf extends tao_helpers_form_Validator implements CrossElementEvaluationAware
{

    /** @var tao_helpers_form_FormElement[]|[] */
    private $references = [];

    /**
     * @throws common_Exception
     * @throws common_exception_Error
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (!$this->hasOption('reference')) {
            throw new common_Exception(sprintf("No reference provided for %s validator", $this->getName()));
        }
    }

    public function evaluate($values)
    {
        $isEmpty = [empty($values)];
        foreach ($this->references as $reference) {
            $isEmpty[] = empty($reference->getRawValue());
        }
        return count(array_filter($isEmpty)) <= 1;
    }

    public function acknowledge(tao_helpers_form_Form $form): void
    {
        $message = [];
        foreach ($this->getOption('reference', []) as $ref) {
            $ref = $ref instanceof tao_helpers_form_FormElement ? $ref : $form->getElement(
                tao_helpers_Uri::encode($ref)
            );

            $this->references[] = $ref;
            $message[] = $ref->getDescription();
        }

        $this->setMessage(__('This or one of %s must have a value', implode(',', $message)));
    }
}
