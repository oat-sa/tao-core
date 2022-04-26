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
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Service;

use core_kernel_classes_Class;
use oat\tao\model\resources\Contract\ClassCopierInterface;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;

class ClassCopier implements ClassCopierInterface
{
    /** @var RootClassesListServiceInterface */
    private $rootClassesListService;

    /** @var ClassPropertyCopier */
    private $classPropertyCopier;

    /** @var InstanceCopier */
    private $instanceCopier;

    public function __construct(
        RootClassesListServiceInterface $rootClassesListService,
        ClassPropertyCopier $classPropertyCopier,
        InstanceCopier $instanceCopier
    ) {
        $this->rootClassesListService = $rootClassesListService;
        $this->classPropertyCopier = $classPropertyCopier;
        $this->instanceCopier = $instanceCopier;
    }

    public function supports(core_kernel_classes_Class $class, core_kernel_classes_Class $destinationClass): bool
    {
        foreach ($this->rootClassesListService->list() as $rootClass) {
            if ($class->isSubClassOf($rootClass)) {
                return $destinationClass->isSubClassOf($rootClass);
            }
        }

        return false;
    }

    public function copy(
        core_kernel_classes_Class $class,
        core_kernel_classes_Class $destinationClass
    ): core_kernel_classes_Class {
        $newClass = $destinationClass->createSubClass($class->getLabel());

        $properties = $class->getProperties(false);
//        $destinationClassProperties = $destinationClass->getProperties(true);
//        $destinationClassPropertiesCache = [];

        foreach ($properties as $index => $property) {
//            if (array_key_exists($property->getUri(), $destinationClassPropertiesCache)) {
//                unset(
//                    $properties[$index],
//                    $destinationClassProperties[$destinationClassPropertiesCache[$property->getUri()]]
//                );
//
//                continue;
//            }
//
//            foreach ($destinationClassProperties as $destinationClassPropertyIndex => $destinationClassProperty) {
//                if ($property->getUri() === $destinationClassProperty->getUri()) {
//                    unset(
//                        $properties[$index],
//                        $destinationClassProperties[$destinationClassPropertyIndex]
//                    );
//
//                    break;
//                }
//
//                $destinationClassPropertiesCache[$destinationClassProperty->getUri()] = $index;
//            }

            $this->classPropertyCopier->copy($property, $newClass);
        }

        foreach ($class->getInstances() as $instance) {
            $this->instanceCopier->copy($instance, $newClass);
        }

        foreach ($class->getSubClasses() as $subClass) {
            $this->copy($subClass, $newClass);
        }

        return $newClass;
    }
}
