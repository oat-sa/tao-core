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

// @todo We may wan to have this in a "Service" namespace instead
namespace oat\tao\model\RdfObjectMapper\Hydrator;

use oat\tao\model\RdfObjectMapper\Annotation\RdfAttributeMapping;
use oat\tao\model\RdfObjectMapper\Annotation\RdfResourceAttributeMapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

use core_kernel_classes_Resource;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Exception;

class ResourceHydrator
{
    /** @var AnnotationReader */
    private $reader;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
            $this->logger->info("Handling property: {$property->getName()}");

            $propertyAnnotations = $this->reader->getPropertyAnnotations(
                $property
            );

            $this->logger->debug("Property {$property->getName()} annotations");

            foreach ($propertyAnnotations as $annotation)
            {
                if($annotation instanceof RdfResourceAttributeMapping)
                {
                    $this->logger->debug('-> Using RdfResourceAttributeMapping');
                    $annotation->hydrate($this->logger, $property, $src, $targetObject);
                    continue;
                }

                if($annotation instanceof RdfAttributeMapping)
                {
                    $this->logger->debug('-> Using RdfAttributeMapping');
                    $annotation->hydrate($this->logger, $property, $src, $targetObject);
                    continue;
                }

                throw new Exception(
                    "Unknown class property type: " . get_class($annotation)
                );
            }
        }
    }
}
