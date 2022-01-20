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
 * Copyright (c) 2022 Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\model\password;

use oat\oatbox\service\ConfigurableService;
use tao_helpers_form_Validator;

class PasswordConstraintsService extends ConfigurableService implements PasswordConstraintsServiceInterface
{
    /** @var tao_helpers_form_Validator[] */
    private array $validators = [];

    public function validate($password): bool
    {
        $result = true;
        foreach ($this->getValidators() as $validator) {
            $result &= $validator->evaluate($password);
        }

        return (bool) $result;
    }

    public function getErrors(): array
    {
        $errors = [];
        foreach ($this->getValidators() as $validator) {
            $errors[] = $validator->getMessage();
        }

        return $errors;
    }

    public function getValidators(): array
    {
        if (empty($this->validators)) {
            $this->validators = $this->loadValidators();
        }

        return $this->validators;
    }

    private function loadValidators(): array
    {
        $validatorsLoader =  new PasswordValidatorLoader();
        $this->validators = $validatorsLoader->load(
            $this->getOption(self::OPTION_CONSTRAINTS)
        );

        return $this->validators;
    }
}
