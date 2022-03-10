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

namespace oat\tao\model\RdfObjectMapper;

use oat\tao\model\RdfObjectMapper\TargetTypes\RdfResourceAttributeMapping;
use oat\tao\model\RdfObjectMapper\TargetTypes\ResourceHydrator;
use RdfAttributeMapping;
use core_kernel_classes_Resource;
use ReflectionClass;
use ReflectionException;

require_once __DIR__ . '/Annotation/RdfAttributeMapping.php';
require_once __DIR__ . '/Annotation/RdfResourceAttributeMapping.php';
require_once __DIR__ . '/Hydrator/ResourceHydrator.php';

// taoDockerize uses PHP 7.2 and Generis is supporting "php": "^7.1":
// we cannot use native annotations.
//
// However, Generis explicitly depends on doctrine/annotations ~1.6.0,
// we may reimplement this using Doctrine annotations instead.
//
// Maybe this should be in Generis instead?
// @todo The object mapper may be an interface with right now a single
//       implementation that uses PHPDoc annotations. Maybe the object mapper
//       can just use delegate the mapping to a "child" mapper with something
//       like a chain of responsibility, so we can have a mapper based on
//       Doctrine annotations while also having the possibility to implement a
//       different one using PHP native annotations in the future.
class RdfObjectMapper
{
    /** @var ResourceHydrator */
    private $hydrator;

    public function __construct()
    {
        $this->hydrator = new ResourceHydrator();
    }

    public function mapResource(
        core_kernel_classes_Resource $resource,
        string $targetClass
    ): object
    {
        $reflector = $this->reflect($targetClass);
        $instance = $reflector->newInstanceWithoutConstructor();

        $this->hydrator->hydrateInstance($reflector, $resource, $instance);

        echo "instance hydrated:<br/>";
        echo "<pre>".var_export($instance, true)."</pre>";

        $this->callConstructorIfPresent($reflector, $instance);

        return $instance;
    }

    /**
     * @throws ReflectionException
     */
    private function reflect(string $targetClass): ReflectionClass
    {
        return new ReflectionClass($targetClass);
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
