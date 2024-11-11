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
 * Copyright (c) 2022-2023 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Service;

use core_kernel_classes_Resource;
use InvalidArgumentException;
use core_kernel_classes_Class;
use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\ClassCopierInterface;
use oat\tao\model\resources\Contract\PermissionCopierInterface;
use oat\tao\model\resources\Contract\ClassMetadataCopierInterface;
use oat\tao\model\resources\Contract\ClassMetadataMapperInterface;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;
use oat\tao\model\resources\ResourceTransferResult;
use oat\tao\model\TaoOntology;

class ClassCopier implements ClassCopierInterface, ResourceTransferInterface
{
    private RootClassesListServiceInterface $rootClassesListService;
    private ClassMetadataCopierInterface $classMetadataCopier;
    private ResourceTransferInterface $instanceCopier;
    private ClassMetadataMapperInterface $classMetadataMapper;
    private PermissionCopierInterface $permissionCopier;
    private Ontology $ontology;
    private array $copiedClasses = [];
    private bool $assertionCompleted = false;

    public function __construct(
        RootClassesListServiceInterface $rootClassesListService,
        ClassMetadataCopierInterface $classMetadataCopier,
        ResourceTransferInterface $instanceCopier,
        ClassMetadataMapperInterface $classMetadataMapper,
        Ontology $ontology
    ) {
        $this->rootClassesListService = $rootClassesListService;
        $this->classMetadataCopier = $classMetadataCopier;
        $this->instanceCopier = $instanceCopier;
        $this->classMetadataMapper = $classMetadataMapper;
        $this->ontology = $ontology;
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
        foreach ($copiers as $copier) {
            $this->withPermissionCopier($copier);
        }
    }

    public function transfer(ResourceTransferCommand $command): ResourceTransferResult
    {
        $class = $this->ontology->getClass($command->getFrom());
        $destinationClass = $this->ontology->getClass($command->getTo());
        $newClass = $this->doCopy($class, $destinationClass, $command->keepOriginalAcl());

        return new ResourceTransferResult($newClass->getUri());
    }

    public function copy(
        core_kernel_classes_Class $class,
        core_kernel_classes_Class $destinationClass
    ): core_kernel_classes_Class {
        return $this->doCopy($class, $destinationClass);
    }

    private function doCopy(
        core_kernel_classes_Class $class,
        core_kernel_classes_Class $destinationClass,
        bool $keepOriginalPermission = true
    ): core_kernel_classes_Class {
        if (in_array($class->getUri(), $this->copiedClasses, true)) {
            return $class;
        }

        $this->assertInSameRootClass($class, $destinationClass);

        $newClass = $destinationClass->createSubClass($class->getLabel());
        $newClassUri = $newClass->getUri();

        $this->copiedClasses[] = $newClassUri;

        $this->classMetadataCopier->copy($class, $newClass);

        if (isset($this->permissionCopier)) {
            $this->permissionCopier->copy(
                $keepOriginalPermission ? $class : $destinationClass,
                $newClass
            );
        }

        foreach ($class->getInstances() as $instance) {
            if ($this->isTranslationInstance($instance)) {
                continue;
            }

            $aclMode = $keepOriginalPermission ?
                ResourceTransferCommand::ACL_KEEP_ORIGINAL :
                ResourceTransferCommand::ACL_USE_DESTINATION;

            $this->instanceCopier->transfer(
                new ResourceTransferCommand(
                    $instance->getUri(),
                    $newClassUri,
                    $aclMode,
                    ResourceTransferCommand::TRANSFER_MODE_COPY
                )
            );
        }

        foreach ($class->getSubClasses() as $subClass) {
            $this->doCopy($subClass, $newClass, $keepOriginalPermission);
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

    private function isTranslationInstance(core_kernel_classes_Resource $instance): bool
    {
        $originalProperty = $instance->getProperty(TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI);

        return $originalProperty && !empty($instance->getOnePropertyValue($originalProperty));
    }
}
