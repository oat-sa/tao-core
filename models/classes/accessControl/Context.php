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

namespace oat\tao\model\accessControl;

use oat\oatbox\user\User;
use InvalidArgumentException;
use oat\tao\model\Context\AbstractContext;

class Context extends AbstractContext
{
    public const PARAM_CONTROLLER = 'controller';
    public const PARAM_ACTION = 'action';
    public const PARAM_USER = 'user';

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_CONTROLLER,
            self::PARAM_ACTION,
            self::PARAM_USER,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        if (
            in_array($parameter, [self::PARAM_CONTROLLER, self::PARAM_ACTION], true)
            && ($parameterValue === null || is_string($parameterValue))
        ) {
            return;
        }

        if (
            $parameter === self::PARAM_USER
            && ($parameterValue === null || $parameterValue instanceof User)
        ) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Context parameter %s is not valid.',
                $parameter
            )
        );
    }
}
