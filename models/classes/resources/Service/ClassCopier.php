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

use InvalidArgumentException;
use core_kernel_classes_Class;
use oat\tao\model\resources\Contract\ClassCopierInterface;
use oat\tao\model\resources\Contract\InstanceCopierInterface;
use oat\tao\model\resources\Contract\PermissionCopierInterface;
use oat\tao\model\resources\Contract\ClassMetadataCopierInterface;
use oat\tao\model\resources\Contract\ClassMetadataMapperInterface;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;

class ClassCopier implements ClassCopierInterface
{
    /** @var RootClassesListServiceInterface */
    private $rootClassesListService;

    /** @var ClassMetadataCopier */
    private $classMetadataCopier;

    /** @var InstanceCopierInterface */
    private $instanceCopier;

    /** @var ClassMetadataMapperInterface */
    private $classMetadataMapper;

    /** @var PermissionCopierInterface */
    private $permissionCopier;

    /** @var string[] */
    private $copiedClasses = [];

    /** @var bool */
    private $assertionCompleted = false;

    public function __construct(
        RootClassesListServiceInterface $rootClassesListService,
        ClassMetadataCopierInterface $classMetadataCopier,
        InstanceCopierInterface $instanceCopier,
        ClassMetadataMapperInterface $classMetadataMapper
    ) {
        $this->rootClassesListService = $rootClassesListService;
        $this->classMetadataCopier = $classMetadataCopier;
        $this->instanceCopier = $instanceCopier;
        $this->classMetadataMapper = $classMetadataMapper;
    }

    public function withPermissionCopier(PermissionCopierInterface $permissionCopier): void
    {
        $this->permissionCopier = $permissionCopier;
    }

    /**
     * This method is to be used with tagged_iterator() from service providers
     * (but only the last copier from the iterable is effectively applied).
     */
    public function withPermissionCopiers(iterable $copiers): void
    {
        foreach($copiers as $copier) {
            $this->withPermissionCopier($copier);
        }
    }

    /**
     * @inheritDoc
     */
    public function copy(
        core_kernel_classes_Class $class,
        core_kernel_classes_Class $destinationClass
    ): core_kernel_classes_Class {
        if (in_array($class->getUri(), $this->copiedClasses, true)) {
            return $class;
        }

        $this->assertInSameRootClass($class, $destinationClass);

        $newClass = $destinationClass->createSubClass($class->getLabel());
        $this->copiedClasses[] = $newClass->getUri();

        $this->classMetadataCopier->copy($class, $newClass);

        foreach ($class->getInstances() as $instance) {
            $this->instanceCopier->copy($instance, $newClass);
        }

        foreach ($class->getSubClasses() as $subClass) {
            $this->copy($subClass, $newClass);
        }

        if (isset($this->permissionCopier)) {
            $this->permissionCopier->copy($class, $newClass);
        }

        $this->classMetadataMapper->remove($newClass->getProperties());

        return $newClass;
    }

    private function assertInSameRootClass(
        core_kernel_classes_Class $class,
        core_kernel_classes_Class $destinationClass
    ): void {
        if ($this->assertionCompleted) {
            return;
        }

        foreach ($this->rootClassesListService->list() as $rootClass) {
            if (
                ($class->equals($rootClass) || $class->isSubClassOf($rootClass))
                && !$destinationClass->equals($rootClass)
                && !$destinationClass->isSubClassOf($rootClass)
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Selected class (%s) and destination class (%s) must be in the same root class (%s).',
                        $class->getUri(),
                        $destinationClass->getUri(),
                        $rootClass->getUri()
                    )
                );
            }
        }

        $this->assertionCompleted = true;
    }
}
