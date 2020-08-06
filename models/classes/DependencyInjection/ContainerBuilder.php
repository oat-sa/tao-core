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

use DI\ContainerBuilder as DiContainerBuilder;
use Psr\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/*
Example: @TODO This comment will be removed on final version:

$containerBuilder = new ContainerBuilder($this->getServiceLocator());
$containerBuilder->addDefinition(new ExampleContainerDefinition());
$container = $containerBuilder->build();
$container->get(ExampleClass::class)->test();
*/
class ContainerBuilder extends DiContainerBuilder
{
    /** @var ServiceLocatorInterface */
    private $serviceLocator;

    /** @var ContainerDefinitionInterface[] */
    private $definitions;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        parent::__construct(Container::class);

        $this->serviceLocator = $serviceLocator;
        $this->definitions = [];
    }

    public function addDefinition(ContainerDefinitionInterface $definition): self
    {
        $this->definitions = array_merge($this->definitions, $definition->getDefinitions());

        return $this;
    }

    public function build(): ContainerInterface
    {
        parent::addDefinitions($this->definitions);

        /** @var Container $container */
        $container = parent::build();
        $container->setServiceLocator($this->serviceLocator);

        return $container;
    }
}