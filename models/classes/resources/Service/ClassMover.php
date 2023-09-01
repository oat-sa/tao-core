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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Service;

use core_kernel_classes_Class;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\event\EventManager;
use oat\tao\model\event\ClassMovedEvent;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\PermissionCopierInterface;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;
use oat\tao\model\resources\ResourceTransferResult;
use oat\tao\model\Specification\ClassSpecificationInterface;

class ClassMover implements ResourceTransferInterface
{
    private Ontology $ontology;
    private ClassSpecificationInterface $rootClassSpecification;
    private RootClassesListServiceInterface $rootClassesListService;
    private EventManager $eventManager;
    private PermissionCopierInterface $permissionCopier;

    public function __construct(
        Ontology $ontology,
        ClassSpecificationInterface $rootClassSpecification,
        RootClassesListServiceInterface $rootClassesListService,
        EventManager $eventManager
    ) {
        $this->ontology = $ontology;
        $this->rootClassSpecification = $rootClassSpecification;
        $this->rootClassesListService = $rootClassesListService;
        $this->eventManager = $eventManager;
    }

    public function withPermissionCopier(PermissionCopierInterface $permissionCopier): void
    {
        $this->permissionCopier = $permissionCopier;
    }

    /**
     * This method is to be used with tagged_iterator() from service providers
     * (but only the last copier from the iterable is effectively applied).
     */
    public function withPermissionCopiers(iterable $permissionCopiers): void
    {
        foreach ($permissionCopiers as $copier) {
            $this->withPermissionCopier($copier);
        }
    }

    public function transfer(ResourceTransferCommand $command): ResourceTransferResult
    {
        $from = $this->ontology->getClass($command->getFrom());
        $to = $this->ontology->getClass($command->getTo());

        $this->assertIsNotRootClass($from);
        $this->assertInSameRootClass($from, $to);
        $this->assertIsNotSameClass($from, $to);
        $this->assertIsNotSubclass($from, $to);

        $status = $from->editPropertyValues($this->ontology->getProperty(OntologyRdfs::RDFS_SUBCLASSOF), $to);

        if ($status) {
            $this->eventManager->trigger(new ClassMovedEvent($from));

            if (isset($this->permissionCopier) && $command->useDestinationAcl()) {
                $this->changePermissions($to, $from);
            }
        }

        return new ResourceTransferResult($from->getUri());
    }

    private function changePermissions(
        core_kernel_classes_Class $source,
        core_kernel_classes_Class $destination
    ): void {
        $this->permissionCopier->copy($source, $destination);

        foreach ($destination->getInstances() as $instance) {
            $this->permissionCopier->copy($source, $instance);
        }

        foreach ($destination->getSubClasses() as $subClass) {
            $this->changePermissions($destination, $subClass);
        }
    }

    private function assertIsNotRootClass(core_kernel_classes_Class $class): void
    {
        if ($this->rootClassSpecification->isSatisfiedBy($class)) {
            throw new InvalidArgumentException(sprintf('Root class "%s" cannot be moved', $class->getUri()));
        }
    }

    private function assertInSameRootClass(
        core_kernel_classes_Class $source,
        core_kernel_classes_Class $destionation
    ): void {
        foreach ($this->rootClassesListService->list() as $rootClass) {
            if (
                ($source->equals($rootClass) || $source->isSubClassOf($rootClass))
                && !$destionation->equals($rootClass)
                && !$destionation->isSubClassOf($rootClass)
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Selected class (%s) and destination class (%s) must be in the same root class (%s).',
                        $source->getUri(),
                        $destionation->getUri(),
                        $rootClass->getUri()
                    )
                );
            }
        }
    }

    private function assertIsNotSameClass(
        core_kernel_classes_Class $source,
        core_kernel_classes_Class $destination
    ): void {
        if ($source->equals($destination)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Selected class (%s) and destination class (%s) cannot be the same class.',
                    $source->getUri(),
                    $destination->getUri()
                )
            );
        }
    }

    private function assertIsNotSubclass(
        core_kernel_classes_Class $source,
        core_kernel_classes_Class $destination
    ): void {
        if ($destination->isSubClassOf($source)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The destination class (%s) cannot be a subclass of the selected class (%s).',
                    $destination->getUri(),
                    $source->getUri()
                )
            );
        }
    }
}
