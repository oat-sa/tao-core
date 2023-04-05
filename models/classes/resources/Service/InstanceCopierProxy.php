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
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Service;

use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;
use oat\tao\model\resources\ResourceTransferResult;
use core_kernel_classes_Class;

class InstanceCopierProxy implements ResourceTransferInterface
{
    /** @var ResourceTransferInterface[] */
    private array $instanceCopiers;
    private RootClassesListServiceInterface $rootClassesListService;
    private Ontology $ontology;

    public function __construct(RootClassesListServiceInterface $rootClassesListService, Ontology $ontology)
    {
        $this->rootClassesListService = $rootClassesListService;
        $this->ontology = $ontology;
    }

    public function addInstanceCopier(string $rootClassUri, ResourceTransferInterface $copier): void
    {
        $this->instanceCopiers[$rootClassUri] = $copier;
    }

    public function transfer(ResourceTransferCommand $command): ResourceTransferResult
    {
        $destinationClass = $this->ontology->getClass($command->getTo());

        return $this->getCopier($destinationClass)->transfer($command);
    }

    private function getCopier(core_kernel_classes_Class $class): ResourceTransferInterface
    {
        $rootClasUri = $this->getRootClassUri($class);

        if (isset($this->instanceCopiers[$rootClasUri])) {
            return $this->instanceCopiers[$rootClasUri];
        }

        throw new InvalidArgumentException(
            sprintf(
                'There is no instance copier mapper for root class %s',
                $rootClasUri
            )
        );
    }

    private function getRootClassUri(core_kernel_classes_Class $class): string
    {
        foreach ($this->rootClassesListService->list() as $rootClass) {
            if ($class->equals($rootClass) || $class->isSubClassOf($rootClass)) {
                return $rootClass->getUri();
            }
        }

        throw new InvalidArgumentException(
            sprintf(
                'Provided class %s does not belong to any root class',
                $class->getUri()
            )
        );
    }
}
