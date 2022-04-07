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

namespace oat\tao\model\RdfObjectMapper\Annotation;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use Psr\Log\LoggerInterface;
use ReflectionProperty;

//#[\Attribute(\Attribute::TARGET_PROPERTY)]
/**
 *@Annotation
 */
class RdfAttributeMapping
{
    public /*string*/ $propertyUri;
    public /*string*/ $attributeType = 'resource';
    public /*string*/ $mappedField = null;

    // the commented out constructors are in case we use PHP 8 annotations
    /*public function __construct(
        string $propertyUri,
        string $attributeType = 'resource',
        string $mappedField  = ''
    )
    {
        $this->propertyUri   = $propertyUri;
        $this->attributeType = $attributeType;
        $this->mappedField   = $mappedField;
    }*/

    /*public function __construct() //($data, $b, $c)
    {
        //$this->attributeType = $attributeType;

        //echo "Called RdfAttributeMapping ctor with ".var_export($data,true);
    }*/

    // You may see keeping hydrate() here in the annotation class as a good
    // thing (as in "higher cohesion", keeping the value initialization in the
    // annotation implementation) or bad thing (as in "higher coupling",
    // preventing having more than one behaviour (implementation) for a given
    // annotation).
    //
    // Maybe we'll want to allow overriding the behaviour for a given annotation
    // somehow in the future (for example, from extensions)).
    //
    public function hydrate(
        LoggerInterface $logger,
        ReflectionProperty $property,
        core_kernel_classes_Resource $src,
        object $targetObject
    ): void
    {
        $logger->debug(__CLASS__ . " maps a value to the property");

        $values = $src->getPropertyValues(
            new core_kernel_classes_Property($this->propertyUri)
        );

        if(count($values) == 0) {
            $logger->warning(__CLASS__ . " no value to map");
            return;
        }

        if(count($values) > 1) {
            $logger->warning(__CLASS__ . "too many values to map");
            return;
        }

        if(count($values) == 1) {
            $value = current($values);
            $logger->info(
                sprintf("%s Mapping value %s into %s",
                    __CLASS__,
                    $value,
                    $property->getName()
                )
            );

            $property->setAccessible(true);
            $property->setValue($targetObject, $value);
        }
    }
}
