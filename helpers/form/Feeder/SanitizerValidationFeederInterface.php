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

use RuntimeException;
use tao_helpers_form_Form;
use tao_helpers_form_Validator;
use tao_helpers_form_FormElement;

interface SanitizerValidationFeederInterface
{
    public function addValidator(tao_helpers_form_Validator $validator): self;

    public function setForm(tao_helpers_form_Form $form): self;

    /**
     * @throws RuntimeException
     */
    public function addElement(tao_helpers_form_FormElement $element): self;

    /**
     * @throws RuntimeException
     */
    public function addElementByUri(string $elementUri): self;

    public function feed(): void;
}
