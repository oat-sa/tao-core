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

use oat\generis\model\data\Ontology;
use oat\oatbox\event\EventManager;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\resources\Event\InstanceCopiedEvent;
use oat\tao\model\resources\ResourceTransferResult;
use RuntimeException;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\tao\model\resources\Contract\InstanceCopierInterface;
use oat\tao\model\resources\Contract\PermissionCopierInterface;
use oat\tao\model\resources\Contract\InstanceContentCopierInterface;
use oat\tao\model\resources\Contract\InstanceMetadataCopierInterface;

class InstanceCopier implements InstanceCopierInterface, ResourceTransferInterface
{
    private InstanceMetadataCopierInterface $instanceMetadataCopier;
    private InstanceContentCopierInterface $instanceContentCopier;
    private PermissionCopierInterface $permissionCopier;
    private Ontology $ontology;
    private EventManager $eventManager;

    public function __construct(InstanceMetadataCopierInterface $instanceMetadataCopier, Ontology $ontology)
    {
        $this->instanceMetadataCopier = $instanceMetadataCopier;
        $this->ontology = $ontology;
    }

    public function withInstanceContentCopier(InstanceContentCopierInterface $instanceContentCopier): void
    {
        $this->instanceContentCopier = $instanceContentCopier;
    }

    public function withPermissionCopier(PermissionCopierInterface $permissionCopier): void
    {
        $this->permissionCopier = $permissionCopier;
    }

    public function withEventManager(EventManager $eventManager): void
    {
        $this->eventManager = $eventManager;
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
        $instance = $this->ontology->getResource($command->getFrom());
        $destinationClass = $this->ontology->getClass($command->getTo());
        $newInstance = $this->doCopy($instance, $destinationClass, $command->keepOriginalAcl());

        return new ResourceTransferResult($newInstance->getUri());
    }

    public function copy(
        core_kernel_classes_Resource $instance,
        core_kernel_classes_Class $destinationClass
    ): core_kernel_classes_Resource {
        return $this->doCopy($instance, $destinationClass);
    }

    private function doCopy(
        core_kernel_classes_Resource $instance,
        core_kernel_classes_Class $destinationClass,
        bool $keepOriginalPermissions = true
    ): core_kernel_classes_Resource {
        $newInstance = $destinationClass->createInstance($instance->getLabel());

        if ($newInstance === null) {
            throw new RuntimeException(
                sprintf(
                    'New instance was not created. Original instance uri: %s, destination class uri: %s',
                    $instance->getUri(),
                    $destinationClass->getUri()
                )
            );
        }

        $this->instanceMetadataCopier->copy($instance, $newInstance);

        if (isset($this->instanceContentCopier)) {
            $this->instanceContentCopier->copy($instance, $newInstance);
        }

        if (isset($this->permissionCopier)) {
            $this->permissionCopier->copy(
                $keepOriginalPermissions ? $instance : $destinationClass,
                $newInstance
            );
        }

        if (isset($this->eventManager)) {
            $this->eventManager->trigger(new InstanceCopiedEvent($newInstance->getUri()));
        }

        return $newInstance;
    }
}
