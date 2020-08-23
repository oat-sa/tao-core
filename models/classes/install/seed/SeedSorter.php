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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\install\seed;

use ReflectionClass;
use ReflectionException;

class SeedSorter
{
    /**
     * @throws ReflectionException
     */
    public function sort(array $config): array
    {
        $sortedConfig = [];

        foreach ($config as $serviceKey => $serviceConfig) {
            $this->sortDependencies($config, $serviceKey, $sortedConfig);
        }

        return $sortedConfig;
    }

    /**
     * @throws ReflectionException
     */
    private function getDependencies(string $className): array
    {
        $reflection = new ReflectionClass($className);

        $constructor = $reflection->getConstructor();

        if (null === $constructor) {
            return [];
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            if ($class = $parameter->getClass()) {
                $dependencies[] = $class->name;
            }
        }

        return $dependencies;
    }

    /**
     * @throws ReflectionException
     */
    private function sortDependencies(array $config, string $serviceKey, array &$result): void
    {
        if (isset($result[$serviceKey])) {
            return;
        }

        if (!isset($config[$serviceKey]['class'])) {
            $result[$serviceKey] = $config[$serviceKey];

            return;
        }

        $serviceClassName = $config[$serviceKey]['class'];

        $dependencies = $this->getDependencies($serviceClassName);

        foreach ($dependencies as $dependencyClassName) {
            try {
                $dependencyKey = $this->findServiceKey($config, $dependencyClassName);
                $this->sortDependencies($config, $dependencyKey, $result);
            } catch (SeedSorterException $e) {
            }
        }

        $result[$serviceKey] = $config[$serviceKey];
    }

    private function findServiceKey(array $config, string $className)
    {
        foreach ($config as $serviceKey => $serviceConfig) {
            if (empty($serviceConfig['class'])) {
                throw new SeedSorterException(
                    sprintf('%s not a class', $className)
                );
            }

            if (
                $serviceConfig['class'] === $className
                || is_subclass_of($serviceConfig['class'], $className)
            ) {
                return $serviceKey;
            }
        }

        throw new SeedSorterException(
            sprintf('Service key for class %s not found', $className)
        );
    }
}
