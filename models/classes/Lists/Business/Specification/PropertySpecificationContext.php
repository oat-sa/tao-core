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

namespace oat\tao\model\Lists\Business\Specification;

use oat\tao\model\Context\AbstractContext;

class PropertySpecificationContext extends AbstractContext
{
    public const PARAM_PROPERTY = 'property';
    public const PARAM_FORM_INDEX = 'form-index';
    public const PARAM_FORM_DATA = 'form-data';

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_PROPERTY,
            self::PARAM_FORM_INDEX,
            self::PARAM_FORM_DATA,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        //@TODO Add validation
    }
}
