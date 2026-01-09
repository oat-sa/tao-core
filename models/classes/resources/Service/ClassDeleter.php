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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Service;

use oat\tao\model\resources\relation\FindAllQuery;
use oat\tao\model\resources\relation\ResourceRelationCollection;
use oat\tao\model\resources\relation\service\ResourceRelationServiceProxy;
use oat\tao\model\TaoOntology;
use Throwable;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use oat\generis\model\data\Ontology;
use oat\tao\model\search\index\OntologyIndex;
use oat\tao\model\accessControl\PermissionCheckerInterface;
use oat\tao\model\resources\Contract\ClassDeleterInterface;
use oat\tao\model\Specification\ClassSpecificationInterface;
use oat\tao\model\resources\Exception\ClassDeletionException;
use oat\generis\model\resource\Context\ResourceRepositoryContext;
use oat\generis\model\resource\Contract\ResourceRepositoryInterface;
use oat\tao\model\resources\Exception\PartialClassDeletionException;

class ClassDeleter implements ClassDeleterInterface
{
    private const RELATION_RESOURCE_MAP = [
        TaoOntology::CLASS_URI_ITEM => 'itemClass'
    ];
    private const PROPERTY_INDEX = OntologyIndex::PROPERTY_INDEX;

    /** @var ClassSpecificationInterface */
    private $rootClassSpecification;

    /** @var PermissionCheckerInterface */
    private $permissionChecker;

    /** @var Ontology */
    private $ontology;

    /** @var ResourceRepositoryInterface */
    private $resourceRepository;

    /** @var ResourceRepositoryInterface */
    private $classRepository;

    /** @var core_kernel_classes_Property */
    private $propertyIndex;

    /** @var core_kernel_classes_Class|null */
    private $selectedClass;
    private ResourceRelationServiceProxy $resourceRelationServiceProxy;

    public function __construct(
        ClassSpecificationInterface $rootClassSpecification,
        PermissionCheckerInterface $permissionChecker,
        Ontology $ontology,
        ResourceRepositoryInterface $resourceRepository,
        ResourceRepositoryInterface $classRepository,
        ResourceRelationServiceProxy $resourceRelationServiceProxy
    ) {
        $this->rootClassSpecification = $rootClassSpecification;
        $this->permissionChecker = $permissionChecker;
        $this->ontology = $ontology;
        $this->resourceRepository = $resourceRepository;
        $this->classRepository = $classRepository;
        $this->resourceRelationServiceProxy = $resourceRelationServiceProxy;

        $this->propertyIndex = $ontology->getProperty(self::PROPERTY_INDEX);
    }

    public function delete(core_kernel_classes_Class $class): void
    {
        if ($this->rootClassSpecification->isSatisfiedBy($class)) {
            throw new ClassDeletionException(
                'The class provided for deletion cannot be the root class.',
                __('You cannot delete the root node')
            );
        }

        try {
            $this->selectedClass = $class;
            $this->deleteClassRecursively($class);
        } catch (Throwable $exception) {
            throw new PartialClassDeletionException(
                sprintf(
                    'Unable to delete class "%s::%s" (%s).',
                    $class->getLabel(),
                    $class->getUri(),
                    $exception->getMessage()
                ),
                __('Unable to delete the selected resource')
            );
        }

        if ($class->exists()) {
            throw new PartialClassDeletionException(
                'Some items could not be deleted',
                __('Some items could not be deleted')
            );
        }
    }

    private function deleteClassRecursively(core_kernel_classes_Class $class): bool
    {
        if (!$this->permissionChecker->hasReadAccess($class->getUri())) {
            return false;
        }

        $isClassDeletable = true;

        foreach ($class->getSubClasses() as $subClass) {
            $isClassDeletable = $this->deleteClassRecursively($subClass) && $isClassDeletable;
        }

        return $this->deleteClass($class, $isClassDeletable);
    }

    /**
     * @param bool $isClassDeletable Class is not deletable if it contains at least one protected subclass,
     *                               instance or property
     */
    private function deleteClass(core_kernel_classes_Class $class, bool $isClassDeletable): bool
    {
        if ($this->deleteClassContent($class, $isClassDeletable)) {
            $this->classRepository->delete(
                new ResourceRepositoryContext(
                    [
                        ResourceRepositoryContext::PARAM_CLASS => $class,
                        ResourceRepositoryContext::PARAM_SELECTED_CLASS => $this->selectedClass,
                    ]
                )
            );

            return true;
        }

        return false;
    }

    private function deleteClassContent(core_kernel_classes_Class $class, bool $isClassDeletable): bool
    {
        return $this->deleteInstances($class)
            && $isClassDeletable
            && $this->permissionChecker->hasWriteAccess($class->getUri())
            && $this->deleteProperties($class->getProperties());
    }

    private function deleteInstances(core_kernel_classes_Class $class): bool
    {
        $status = true;
        $resources = $class->getInstances();
        if ($query = $this->createQuery($class)) {
            $itemsInUse = $this->resourceRelationServiceProxy->findRelations($query);
            if ($itemsInUse->jsonSerialize()) {
                $resources = $this->filterInstances($resources, $itemsInUse);
                $status = false;
            }
        }

        foreach ($resources as $instance) {
            if (!$instance->exists()) {
                continue;
            }

            if (!$this->permissionChecker->hasWriteAccess($instance->getUri())) {
                $status = false;

                continue;
            }

            try {
                $this->resourceRepository->delete(
                    new ResourceRepositoryContext(
                        [
                            ResourceRepositoryContext::PARAM_RESOURCE => $instance,
                            ResourceRepositoryContext::PARAM_SELECTED_CLASS => $this->selectedClass,
                            ResourceRepositoryContext::PARAM_PARENT_CLASS => $class,
                        ]
                    )
                );
            } catch (Throwable $exception) {
                $status = false;
            }
        }

        return $status;
    }

    /**
     * @param core_kernel_classes_Property[] $properties
     */
    private function deleteProperties(array $properties): bool
    {
        $status = true;

        foreach ($properties as $property) {
            $status = $this->deleteProperty($property) && $status;
        }

        return $status;
    }

    private function deleteProperty(core_kernel_classes_Property $property): bool
    {
        $indexes = $property->getPropertyValues($this->propertyIndex);

        if (!$property->delete(true)) {
            return false;
        }

        foreach ($indexes as $indexUri) {
            $this->resourceRepository->delete(
                new ResourceRepositoryContext(
                    [
                        ResourceRepositoryContext::PARAM_RESOURCE => $this->ontology->getResource($indexUri),
                        ResourceRepositoryContext::PARAM_DELETE_REFERENCE => true,
                    ]
                )
            );
        }

        return true;
    }

    private function defineResourceType(core_kernel_classes_Class $class): ?string
    {
        if (isset(self::RELATION_RESOURCE_MAP[$class->getRootId()])) {
            return self::RELATION_RESOURCE_MAP[$class->getRootId()];
        }

        return null;
    }

    private function createQuery($class): ?FindAllQuery
    {
        if ($this->defineResourceType($class)) {
            return new FindAllQuery(null, $class->getUri(), $this->defineResourceType($class));
        }

        return null;
    }

    private function filterInstances(array $resourceCollection, ResourceRelationCollection $itemsInUse): iterable
    {
        foreach ($itemsInUse->getIterator() as $item) {
            unset($resourceCollection[$item->getId()]);
        }

        return $resourceCollection;
    }
}
