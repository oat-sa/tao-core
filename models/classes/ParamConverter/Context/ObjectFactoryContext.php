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

namespace oat\tao\model\ParamConverter\Context;

use InvalidArgumentException;
use oat\tao\model\Context\AbstractContext;

class ObjectFactoryContext extends AbstractContext implements ObjectFactoryContextInterface
{
    public const PARAM_CLASS = 'class';
    public const PARAM_DATA = 'data';
    public const PARAM_FORMAT = 'format';
    public const PARAM_CONTEXT = 'context';

    public function getClass(): string
    {
        return $this->getParameter(self::PARAM_CLASS);
    }

    public function getData(): array
    {
        return $this->getParameter(self::PARAM_DATA);
    }

    public function getFormat(): string
    {
        return $this->getParameter(self::PARAM_FORMAT, 'json');
    }

    public function getContext(): array
    {
        return $this->getParameter(self::PARAM_CONTEXT, []);
    }

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_CLASS,
            self::PARAM_DATA,
            self::PARAM_FORMAT,
            self::PARAM_CONTEXT,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        if (
            in_array($parameter, [self::PARAM_CLASS, self::PARAM_FORMAT], true)
            && is_string($parameterValue)
        ) {
            return;
        }

        if (
            in_array($parameter, [self::PARAM_DATA, self::PARAM_CONTEXT], true)
            && is_array($parameterValue)
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
