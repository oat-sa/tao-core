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
use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\ClassCopierInterface;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;
use oat\tao\model\resources\ResourceTransferResult;

class ClassCopierProxy implements ClassCopierInterface, ResourceTransferInterface
{
    private RootClassesListServiceInterface $rootClassesListService;

    /** @var array<string, ClassCopierInterface|ResourceTransferInterface> */
    private array $classCopiers = [];

    private Ontology $ontology;

    public function __construct(RootClassesListServiceInterface $rootClassesListService, Ontology $ontology)
    {
        $this->rootClassesListService = $rootClassesListService;
        $this->ontology = $ontology;
    }

    /**
     * @param ClassCopierInterface|ResourceTransferInterface $classCopier
     */
    public function addClassCopier(string $rootClassUri, $classCopier): void
    {
        if (!$classCopier instanceof ClassCopierInterface && !$classCopier instanceof ResourceTransferInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Class copier not supported: %s',
                    get_class($classCopier)
                )
            );
        }

        if (!in_array($rootClassUri, $this->rootClassesListService->listUris(), true)) {
            throw new InvalidArgumentException('Provided root class URI was not found in root classes list.');
        }

        if (isset($this->classCopiers[$rootClassUri])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Root class (%s) already configured to use copier service (%s)',
                    $rootClassUri,
                    get_class($this->classCopiers[$rootClassUri])
                )
            );
        }

        $this->classCopiers[$rootClassUri] = $classCopier;
    }

    public function transfer(ResourceTransferCommand $command): ResourceTransferResult
    {
        $class = $this->ontology->getClass($command->getFrom());
        $rootClassUri = $this->extractRootClass($class)->getUri();

        if (isset($this->classCopiers[$rootClassUri])) {
            return $this->classCopiers[$rootClassUri]->transfer($command);
        }

        throw new InvalidArgumentException(
            sprintf(
                'Provided class (%s) cannot be copied to the destination class (%s) - not supported by any class copier.',
                $command->getFrom(),
                $command->getTo()
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function copy(
        core_kernel_classes_Class $class,
        core_kernel_classes_Class $destinationClass
    ): core_kernel_classes_Class {
        $rootClassUri = $this->extractRootClass($class)->getUri();

        if (isset($this->classCopiers[$rootClassUri])) {
            return $this->classCopiers[$rootClassUri]->copy($class, $destinationClass);
        }

        throw new InvalidArgumentException(
            sprintf(
                'Provided class (%s) cannot be copied to the destination class (%s) - not supported by any class copier.',
                $class->getUri(),
                $destinationClass->getUri()
            )
        );
    }

    private function extractRootClass(core_kernel_classes_Class $class): core_kernel_classes_Class
    {
        foreach ($this->rootClassesListService->list() as $rootClass) {
            if ($class->equals($rootClass) || $class->isSubClassOf($rootClass)) {
                return $rootClass;
            }
        }

        throw new InvalidArgumentException('Provided class does not belong to any root class');
    }
}
