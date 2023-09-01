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
use InvalidArgumentException;
use oat\tao\model\Context\AbstractContext;

class ElementFactoryContext extends AbstractContext
{
    public const PARAM_RANGE = 'range';
    public const PARAM_INDEX = 'index';
    public const PARAM_PROPERTY = 'property';
    public const PARAM_DATA = 'data';

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_RANGE,
            self::PARAM_INDEX,
            self::PARAM_PROPERTY,
            self::PARAM_DATA,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        if ($parameter === self::PARAM_PROPERTY && $parameterValue instanceof core_kernel_classes_Property) {
            return;
        }

        if ($parameter === self::PARAM_INDEX && is_int($parameterValue)) {
            return;
        }

        if ($parameter === self::PARAM_DATA && is_array($parameterValue)) {
            return;
        }

        if ($parameter === self::PARAM_RANGE) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Invalid argument for %s',
                $parameter
            )
        );
    }
}
