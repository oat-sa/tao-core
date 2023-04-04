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
use oat\oatbox\service\ServiceManager;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\resources\ResourceTransferResult;

class ResourceTransferProxy implements ResourceTransferInterface
{
    private ResourceTransferInterface $classCopier;
    private ResourceTransferInterface $instanceCopier;
    private ResourceTransferInterface $classMover;
    private ResourceTransferInterface $instanceMover;
    private Ontology $ontology;
    private ClassMetadataCopier $classMetadataCopier;

    public function __construct(
        ResourceTransferInterface $classCopier,
        ResourceTransferInterface $instanceCopier,
        ResourceTransferInterface $classMover,
        ResourceTransferInterface $instanceMover,
        Ontology $ontology,
        ClassMetadataCopier $classMetadataCopier
    ) {
        $this->classCopier = $classCopier;
        $this->instanceCopier = $instanceCopier;
        $this->classMover = $classMover;
        $this->instanceMover = $instanceMover;
        $this->ontology = $ontology;
        $this->classMetadataCopier = $classMetadataCopier;
    }

    public function transfer(ResourceTransferCommand $command): ResourceTransferResult
    {
        //FIXME @TODO Remove after tests
        $command = new ResourceTransferCommand(
            $command->getFrom(),
            $command->getTo(),
            getenv('ACL_TRANSFER_MODE') ?: ResourceTransferCommand::ACL_KEEP_ORIGINAL,
            $command->isCopyTo() ? ResourceTransferCommand::TRANSFER_MODE_COPY : ResourceTransferCommand::TRANSFER_MODE_MOVE
        );
        //FIXME @TODO Remove after tests

        return $this->getTransfer($command)->transfer($command);
    }

    private function getTransfer(ResourceTransferCommand $command): ResourceTransferInterface
    {
        //FIXME @TODO Remove after tests
        \common_Logger::e('=========> RESOURCE TRANSFER (' . __METHOD__ . ') ' . var_export($command, true));
        //FIXME @TODO Remove after tests

        $from = $this->ontology->getResource($command->getFrom());
        $to = $this->ontology->getResource($command->getTo());

        if (!$to->isClass()) {
            throw new InvalidArgumentException(
                sprintf(
                    'The destination resource [%s:%s] is not a class',
                    $command->getTo(),
                    $to->getLabel()
                )
            );
        }

        if ($from->isClass()) {
            if ($command->isCopyTo()) {
                return $this->classCopier;
            }

            return $this->classMover;
        }

        if ($command->isCopyTo()) {
            //FIXME
            //FIXME
            $this->classMetadataCopier->copyForInstance($from, $this->ontology->getClass($command->getTo()));
            //FIXME
            //FIXME

            return $this->instanceCopier;
        }

        return $this->instanceMover;
    }
}
