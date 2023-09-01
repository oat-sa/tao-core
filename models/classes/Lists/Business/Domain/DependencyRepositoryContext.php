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

namespace oat\tao\model\Lists\Business\Domain;

use InvalidArgumentException;
use oat\tao\model\Context\AbstractContext;

class DependencyRepositoryContext extends AbstractContext
{
    public const PARAM_LIST_URIS = 'list_uris';
    public const PARAM_DEPENDENCY_LIST_VALUES = 'dependency_list_values';

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_LIST_URIS,
            self::PARAM_DEPENDENCY_LIST_VALUES,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        if (!is_array($parameterValue)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Context parameter %s is not valid. It should be an array.',
                    $parameter
                )
            );
        }

        foreach ($parameterValue as $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Context parameter %s is not valid. The values must be a string.',
                        $parameter
                    )
                );
            }
        }
    }
}
