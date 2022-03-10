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

use core_kernel_classes_Resource;
use LogicException;
use Psr\Log\LoggerInterface;
use ReflectionProperty;

//#[\Attribute(\Attribute::TARGET_PROPERTY)]
/**
 *@Annotation
 */
class RdfResourceAttributeMapping
{
    public /*int*/ $type = 0;

    // the commented out constructors are in case we use PHP 8 annotations
    //public function __construct(int $attributeType)
    /*public function __construct($data)
    {
        //$this->attributeType = $attributeType;

        //echo "Called RdfResourceAttributeMapping ctor with ".var_export($data,true);
    }*/

    public function hydrate(
        LoggerInterface $logger,
        ReflectionProperty $property,
        core_kernel_classes_Resource $src,
        object $targetObject
    ): void
    {
        $logger->debug(
            __CLASS__ .
            ' maps a (direct) value from the resource class to the property'
        );

        switch ($this->type)
        {
            case RdfResourceAttributeType::LABEL:
                $value = $src->getLabel();
                break;
            case RdfResourceAttributeType::COMMENT:
                $value = $src->getComment();
                break;
            case RdfResourceAttributeType::URI:
                $value = $src->getUri();
                break;
            default:
                throw new LogicException(
                    "Unknown ".__CLASS__."::type value: ".
                    $this->type
                );
        }

        $logger->info(
            sprintf("%s Mapping value %s into %s",
                __CLASS__,
                $value,
                $property->getName()
            )
        );

        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            // Not needed starting PHP 8.1 (it has become a no-op since then)
            $property->setAccessible(true);
        }

        $property->setValue($targetObject, $value);
    }
}
