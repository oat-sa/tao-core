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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

namespace oat\tao\model\RdfObjectMapper\TargetTypes;


require_once __DIR__ . '/../Annotation/RdfAttributeMapping.php';
require_once __DIR__ . '/../Annotation/RdfResourceAttributeMapping.php';
require_once __DIR__ . '/../Annotation/RdfResourceAttributeType.php';

use oat\tao\model\RdfObjectMapper\Annotation\RdfAttributeMapping;
use oat\tao\model\RdfObjectMapper\Annotation\RdfResourceAttributeMapping;
use oat\tao\model\RdfObjectMapper\Annotation\RdfResourceAttributeType;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

use core_kernel_classes_Resource;
use ReflectionClass;
use ReflectionException;
use Exception;

class ResourceHydrator
{
    /** @var AnnotationReader */
    private $reader;

    public function __construct()
    {
        $this->reader = new AnnotationReader();

        // Too bad this pollutes the global annotation reader state
        AnnotationRegistry::loadAnnotationClass(RdfResourceAttributeMapping::class);
        AnnotationRegistry::loadAnnotationClass(RdfAttributeMapping::class);
    }

    public function hydrateInstance(
        ReflectionClass $reflector,
        core_kernel_classes_Resource $src,
        object &$targetObject
    )
    {
        foreach($reflector->getProperties() as $property) {
            echo $property->getName()."<br/>\n";

            $propertyAnnotations = $this->reader->getPropertyAnnotations(
                $property
            );

            echo "Property {$property->getName()} annotations<br/>\n";

            foreach ($propertyAnnotations as $annotation)
            {
                // @todo We may delegate the initialization to the annotation
                //       class itself (i.e. pass a ref to the attribute and the
                //       value)?
                if($annotation instanceof RdfResourceAttributeMapping)
                {
                    echo "-> RdfResourceAttributeMapping<br/>\n";
                    $annotation->hydrate($property, $src, $targetObject);
                    continue;
                }

                if($annotation instanceof RdfAttributeMapping)
                {
                    echo "-> RdfAttributeMapping<br/>\n";
                    $annotation->hydrate($property, $src, $targetObject);
                    continue;
                }

                throw new Exception(
                    "Unknown class property type: " . get_class($annotation)
                );
            }

            echo "<br/>\n", "<br/>\n", "<br/>\n";
        }

    }
}