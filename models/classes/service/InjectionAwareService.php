<?php declare(strict_types=1);

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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\service;

use common_Utils;
use oat\oatbox\service\ConfigurableService;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

abstract class InjectionAwareService extends ConfigurableService
{
    /** @noinspection MagicMethodsValidityInspection */
    public function __toPhpCode(): string
    {
        return sprintf(
            "new %s(\n%s\n)",
            static::class,
            implode(",\n", $this->getSerializedDependencies())
        );
    }

    private function getSerializedDependencies(): array
    {
        return array_map(
            [common_Utils::class, 'toPHPVariableString'],
            $this->getDependencies()
        );
    }

    /**
     * @return array A list of dependencies to be injected in their order.
     * @throws ReflectionException
     */
    protected function getDependencies(): array
    {
        $dependencies = [];

        $class = new ReflectionClass($this);
        $constructor = $class->getMethod('__construct');
        $parameters = $constructor->getParameters();

        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();

            if (!$class->hasProperty($parameterName)) {
                $message = sprintf(
                    'Cannot find property "%s" in class %s. Please name properties exactly like constructor parameters, or overload %s',
                    $parameterName,
                    static::class,
                    __METHOD__
                );
                throw new RuntimeException($message);
            }

            $classProperties = $class->getProperty($parameterName);

            if ($classProperties->isPrivate() || $classProperties->isProtected()) {
                $classProperties->setAccessible(true);
            }

            $dependencies[] = $classProperties->getValue($this);
        }

        return $dependencies;
    }
}
