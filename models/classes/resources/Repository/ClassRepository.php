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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Repository;

use RuntimeException;
use InvalidArgumentException;
use core_kernel_classes_Class;
use oat\oatbox\event\EventManager;
use oat\generis\model\data\Ontology;
use core_kernel_persistence_ClassInterface;
use oat\tao\model\Context\ContextInterface;
use oat\generis\model\data\event\ClassDeletedEvent;
use oat\tao\model\resources\Context\ResourceRepositoryContext;
use oat\tao\model\resources\Contract\ResourceRepositoryInterface;

class ClassRepository implements ResourceRepositoryInterface
{
    /** @var Ontology */
    private $ontology;

    /** @var EventManager */
    private $eventManager;

    public function __construct(Ontology $ontology, EventManager $eventManager)
    {
        $this->ontology = $ontology;
        $this->eventManager = $eventManager;
    }

    public function delete(ContextInterface $context): void
    {
        /** @var core_kernel_classes_Class|null $class */
        $class = $context->getParameter(ResourceRepositoryContext::PARAM_CLASS);

        if ($class === null) {
            throw new InvalidArgumentException('Class was not provided for deletion.');
        }

        $deleteReference = $context->getParameter(
            ResourceRepositoryContext::PARAM_DELETE_REFERENCE,
            false
        );
        /** @var core_kernel_classes_Class $parentClass */
        $parentClass = $context->getParameter(
            ResourceRepositoryContext::PARAM_PARENT_CLASS,
            current($class->getParentClasses()) ?: null
        );

        if (!$this->getImplementation()->delete($class, $deleteReference)) {
            throw new RuntimeException(
                sprintf(
                    'Class "%s" ("%s") was not deleted.',
                    $class->getLabel(),
                    $class->getUri()
                )
            );
        }

        /** @var core_kernel_classes_Class|null $selectedClass */
        $selectedClass = $context->getParameter(ResourceRepositoryContext::PARAM_SELECTED_CLASS);
        $classDeletedEvent = (new ClassDeletedEvent($class))
            ->setSelectedClass($selectedClass)
            ->setParentClass($parentClass);
        $this->eventManager->trigger($classDeletedEvent);
    }

    private function getImplementation(): core_kernel_persistence_ClassInterface
    {
        return $this->ontology->getRdfsInterface()->getClassImplementation();
    }
}
