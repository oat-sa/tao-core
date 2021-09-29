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

namespace oat\tao\model\ParamConverter\Request;

use Throwable;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use oat\tao\model\ParamConverter\Configuration\ParamConverter;

abstract class AbstractParamConverter implements ParamConverterInterface
{
    public function getPriority(): int
    {
        return 0;
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        try {
            $object = $this->createObject(
                $this->getData($request, $configuration->getOptions()),
                $configuration->getClass()
            );

            $request->attributes->set($configuration->getName(), $object);
        } catch (Throwable $exception) {
            return false;
        }

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() !== null && $configuration->getConverter() === $this->getName();
    }

    abstract protected function getData(Request $request, array $options): array;

    private function createObject(array $data, string $class): object
    {
        $constructor = new ReflectionMethod($class, '__construct');
        $constructorArgs = [];

        foreach ($constructor->getParameters() as $constructorParameter) {
            $constructorParameterName = $constructorParameter->getName();

            if (array_key_exists($constructorParameterName, $data)) {
                $constructorArgs[$constructorParameterName] = $data[$constructorParameterName];
                unset($data[$constructorParameterName]);
            }
        }

        $instance = (new ReflectionClass($class))->newInstanceArgs($constructorArgs);

        foreach ($data as $queryParameter => $value) {
            if (method_exists($instance, 'set' . $queryParameter)) {
                $instance->{'set' . $queryParameter}($value);
            } elseif (property_exists($instance, $queryParameter)) {
                $instance->$queryParameter = $value;
            }
        }

        return $instance;
    }
}
