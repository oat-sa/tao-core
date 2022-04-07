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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA.
 */

namespace oat\tao\model\RdfObjectMapper\Service;

use oat\tao\model\RdfObjectMapper\Contract\RdfObjectMapperInterface;
use oat\tao\model\RdfObjectMapper\TargetTypes\RdfResourceAttributeMapping;
use Psr\Log\LoggerInterface;
use RdfAttributeMapping;
use core_kernel_classes_Resource;
use ReflectionClass;
use ReflectionException;

// right not this is needed (it seems they are not autoloaded for some reason)
require_once __DIR__ . '/../Annotation/RdfAttributeMapping.php';
require_once __DIR__ . '/../Annotation/RdfResourceAttributeMapping.php';

class RdfObjectMapper implements RdfObjectMapperInterface
{
    /** @var ResourceHydrator */
    private $hydrator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger, ResourceHydrator $hydrator)
    {
        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * @throws ReflectionException
     */
    public function mapResource(
        core_kernel_classes_Resource $resource,
        string $targetClass
    ): object
    {
        $reflector = new ReflectionClass($targetClass);
        $instance = $reflector->newInstanceWithoutConstructor();

        $this->hydrator->hydrateInstance($reflector, $resource, $instance);

        $this->callConstructorIfPresent($reflector, $instance);

        return $instance;
    }

    private function callConstructorIfPresent(
        ReflectionClass $reflector,
        object $instance
    )
    {
        if($reflector->getConstructor() != null)
        {
            $closure = $reflector->getConstructor()->getClosure();
            $closure->call($instance);
        }
    }
}
