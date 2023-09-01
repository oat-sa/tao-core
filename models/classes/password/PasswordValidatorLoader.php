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

class PasswordValidatorLoader implements PasswordValidatorLoaderInterface
{
    public function load(array $config): array
    {
        $validators = [];

        if (array_key_exists('length', $config) && (int) $config['length']) {
            $validators[] = new \tao_helpers_form_validators_Length([ 'min' => (int) $config['length'] ]);
        }

        if (
            (array_key_exists('upper', $config) && $config['upper'])
            || (array_key_exists('lower', $config) && $config['lower'])
        ) {
            $validators[] = new \tao_helpers_form_validators_Regex(
                [
                    'message' => __('Must include at least one letter'),
                    'format'  => '/\pL/'
                ],
                'letters'
            );
        }

        if (array_key_exists('upper', $config) && $config['upper']) {
            $validators[] = new \tao_helpers_form_validators_Regex(
                [
                    'message' => __('Must include upper case letters'),
                    'format'  => '/(\p{Lu}+)/',
                ],
                'caseUpper'
            );
        }

        if (array_key_exists('lower', $config) && $config['lower']) {
            $validators[] = new \tao_helpers_form_validators_Regex(
                [
                    'message' => __('Must include lower case letters'),
                    'format'  => '/(\p{Ll}+)/'
                ],
                'caseLower'
            );
        }

        if (array_key_exists('number', $config) && $config['number']) {
            $validators[] = new \tao_helpers_form_validators_Regex(
                [
                    'message' => __('Must include at least one number'),
                    'format'  => '/\pN/'
                ],
                'number'
            );
        }

        if (array_key_exists('spec', $config) && $config['spec']) {
            $validators[] = new \tao_helpers_form_validators_Regex(
                [
                    'message' => __('Must include at least one special letter'),
                    'format'  => '/[^p{Ll}\p{Lu}\pL\pN]/'
                ],
                'spec'
            );
        }

        return $validators;
    }
}
