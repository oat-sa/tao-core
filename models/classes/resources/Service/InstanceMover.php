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
use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\PermissionCopierInterface;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;
use oat\tao\model\resources\ResourceTransferResult;
use oat\tao\model\Translation\Service\TranslationMoveService;

class InstanceMover implements ResourceTransferInterface
{
    private Ontology $ontology;
    private RootClassesListServiceInterface $rootClassesListService;
    private TranslationMoveService $translationMoveService;
    private PermissionCopierInterface $permissionCopier;

    public function __construct(
        Ontology $ontology,
        RootClassesListServiceInterface $rootClassesListService,
        TranslationMoveService $translationMoveService
    ) {
        $this->ontology = $ontology;
        $this->rootClassesListService = $rootClassesListService;
        $this->translationMoveService = $translationMoveService;
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
        $from = $this->ontology->getResource($command->getFrom());
        $to = $this->ontology->getClass($command->getTo());

        $fromClasses = $from->getTypes();

        $this->assertInSameRootClass($from, current($fromClasses), $to);

        foreach ($fromClasses as $fromClass) {
            $from->removeType($fromClass);
        }

        $from->setType($to);

        if (isset($this->permissionCopier) && $command->useDestinationAcl()) {
            $this->permissionCopier->copy($to, $from);
        }

        $this->translationMoveService->moveTranslations($command);

        return new ResourceTransferResult($from->getUri());
    }

    private function assertInSameRootClass(
        core_kernel_classes_Resource $fromInstance,
        core_kernel_classes_Class $fromClass,
        core_kernel_classes_Class $toClass
    ): void {
        foreach ($this->rootClassesListService->list() as $rootClass) {
            if (
                ($fromClass->equals($rootClass) || $fromClass->isSubClassOf($rootClass))
                && !$toClass->equals($rootClass)
                && !$toClass->isSubClassOf($rootClass)
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Selected instance (%s) and destination class (%s) must be in the same root class (%s).',
                        $fromInstance->getUri(),
                        $toClass->getUri(),
                        $rootClass->getUri()
                    )
                );
            }
        }
    }
}
