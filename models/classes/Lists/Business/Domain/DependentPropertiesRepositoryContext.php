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
use core_kernel_classes_Property;
use oat\tao\model\Context\AbstractContext;

class DependentPropertiesRepositoryContext extends AbstractContext
{
    public const PARAM_PROPERTY = 'property';

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_PROPERTY,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        if ($parameter === self::PARAM_PROPERTY && !$parameterValue instanceof core_kernel_classes_Property) {
            throw new InvalidArgumentException(
                sprintf(
                    'Context parameter %s is not valid. It must be an instance of %s.',
                    $parameter,
                    core_kernel_classes_Property::class
                )
            );
        }
    }
}
