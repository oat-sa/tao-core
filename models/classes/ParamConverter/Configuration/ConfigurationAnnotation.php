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

namespace oat\tao\model\ParamConverter\Configuration;

use RuntimeException;

abstract class ConfigurationAnnotation
{
    public function __construct(array $values)
    {
        foreach ($values as $property => $value) {
            if ($this->setViaSetter($property, $value) || $this->setDirectly($property, $value)) {
                continue;
            }

            throw new RuntimeException(
                sprintf(
                    'Unknown property "%s" for annotation "@%s".',
                    $property,
                    static::class
                )
            );
        }
    }

    private function setViaSetter(string $property, $value): bool
    {
        $isSetterExists = method_exists($this, 'set' . $property);

        if ($isSetterExists) {
            $this->{'set' . $property}($value);
        }

        return $isSetterExists;
    }

    private function setDirectly(string $property, $value): bool
    {
        $isPropertyExists = property_exists($this, $property);

        if ($isPropertyExists) {
            $this->$property = $value;
        }

        return $isPropertyExists;
    }
}
