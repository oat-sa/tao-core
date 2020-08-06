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
 */

namespace oat\tao\model\DependencyInjection;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Throwable;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;

class Container implements ContainerInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var ContainerInterface */
    private $container2;

    /** @var ContainerDefinitionInterface[] */
    private $definitions;

    /** @var ServiceLocatorInterface */
    private $serviceLocator;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        $this->definitions = [];
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        $service = null;

        try {
            $service = $this->serviceLocator->get($id);

            return $service;
        } catch (ServiceNotFoundException $exception) {
            $service = $this->getContainer()->get($id);

            return $service;
        } catch (Throwable $exception) {
            $service = $this->container2->get($id);

            return $service;
        }
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return $this->serviceLocator->has($id) || $this->getContainer()->has($id);
    }

    public function addDefinition(ContainerDefinitionInterface $definition): self
    {
        $this->definitions = array_merge(
            $this->definitions,
            $definition->getDefinitions()
        );

        return $this;
    }

    private function getContainer(): ContainerInterface
    {
        if (!$this->container) {
            $builder = new ContainerBuilder();
            $builder->addDefinitions($this->definitions);

            $this->container2 = $builder->build();

            $this->container = $this;
        }

        return $this->container;
    }
}