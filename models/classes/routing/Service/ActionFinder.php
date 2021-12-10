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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA; *
 */

namespace oat\tao\model\routing\Service;

use Psr\Container\ContainerInterface;
use oat\tao\model\http\Controller;
use ReflectionClass;

class ActionFinder
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function find(string $className): ?object
    {
        $serviceId = $this->getServiceId($className);

        if ($this->container->has($serviceId)) {
            return $this->container->get($serviceId);
        }

        if (!$this->isAutoWiredController($className, $serviceId)) {
            return null;
        }

        $reflectionClass = new ReflectionClass($className);

        $constructorParameters = $reflectionClass->getConstructor()->getParameters();

        if (count($constructorParameters) < 1) {
            return null;
        }

        $params = [];

        foreach ($constructorParameters as $parameter) {
            $paramClass = $parameter->getClass();

            if (!$paramClass) {
                continue;
            }

            $paramClassName = $paramClass->getName();

            $paramServiceId = $this->getServiceId($paramClassName);

            if (!$this->container->has($paramServiceId)) {
                return null;
            }

            $params[] = $this->container->get($paramServiceId);
        }

        return $reflectionClass->newInstanceArgs($params);
    }

    private function getServiceId(string $className): string
    {
        return defined("$className::SERVICE_ID")
            ? $className::SERVICE_ID
            : $className;
    }

    private function isAutoWiredController(string $className, string $serviceId): bool
    {
        return $className === $serviceId
            && class_exists($className)
            && method_exists($className, '__construct')
            && !in_array(Controller::class, class_implements($className), true);
    }
}
