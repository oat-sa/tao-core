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

namespace oat\tao\model\Context;

use InvalidArgumentException;

abstract class AbstractContext implements ContextInterface
{
    protected $parameters = [];

    public function __construct(array $parameters)
    {
        foreach ($parameters as $parameter => $parameterValue) {
            $this->setParameter($parameter, $parameterValue);
        }
    }

    public function getParameter(string $parameter, $default = null)
    {
        $this->checkParameterSupport($parameter);

        return $this->parameters[$parameter] ?? $default;
    }

    public function setParameter(string $parameter, $parameterValue): void
    {
        $this->checkParameterSupport($parameter);
        $this->validateParameter($parameter, $parameterValue);

        $this->parameters[$parameter] = $parameterValue;
    }

    abstract protected function getSupportedParameters(): array;

    /**
     * @param mixed $parameterValue
     */
    abstract protected function validateParameter(string $parameter, $parameterValue): void;

    private function checkParameterSupport(string $parameter): void
    {
        if (!in_array($parameter, $this->getSupportedParameters(), true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Context parameter %s is not supported.',
                    $parameter
                )
            );
        }
    }
}
