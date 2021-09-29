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

use ReflectionType;
use ReflectionFunctionAbstract;
use Symfony\Component\HttpFoundation\Request;

class Configurator implements ConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(ReflectionFunctionAbstract $reflection, Request $request, array &$configurations): void
    {
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            $class = $this->getParamClassByType($type);

            if ($class !== null && $request instanceof $class) {
                continue;
            }

            $name = $parameter->getName();

            if ($type) {
                if (!isset($configurations[$name])) {
                    $configurations[$name] = new ParamConverter($name);
                }

                if ($class !== null && $configurations[$name]->getClass() === null) {
                    $configurations[$name]->setClass($class);
                }

                $configurationClass = $configurations[$name]->getClass();

                if (
                    $configurationClass !== null
                    && $configurations[$name]->getConverter() === null
                    && defined($configurationClass . '::CONVERTER_ID')
                ) {
                    $configurations[$name]->setConverter(
                        constant($configurationClass . '::CONVERTER_ID')
                    );
                }
            }

            if (isset($configurations[$name])) {
                $isOptional = $parameter->isOptional()
                    || $parameter->isDefaultValueAvailable()
                    || ($type && $type->allowsNull());

                $configurations[$name]->setIsOptional($isOptional);
            }
        }
    }

    private function getParamClassByType(?ReflectionType $type): ?string
    {
        return $type !== null && !$type->isBuiltin()
            ? $type->getName()
            : null;
    }
}
