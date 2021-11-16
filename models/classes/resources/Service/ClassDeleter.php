<?php

declare(strict_types=1);

namespace oat\tao\model\resources\Service;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\search\index\OntologyIndex;
use oat\tao\model\accessControl\PermissionCheckerInterface;

class ClassDeleter implements ClassDeleterInterface
{
    private const PROPERTY_INDEX = OntologyIndex::PROPERTY_INDEX;

    /** @var PermissionCheckerInterface */
    private $permissionChecker;

    /** @var Ontology */
    private $ontology;

    public function __construct(PermissionCheckerInterface $permissionChecker, Ontology $ontology)
    {
        $this->permissionChecker = $permissionChecker;
        $this->ontology = $ontology;
    }

    public function delete(core_kernel_classes_Class $class, core_kernel_classes_Class $rootClass): void
    {
        if (!$class->equals($rootClass)) {
            $this->deleteClassRecursively($class);
        }
    }

    public function isDeleted(core_kernel_classes_Class $class): bool
    {
        return !$class->exists();
    }

    private function deleteClassRecursively(core_kernel_classes_Class $class): bool
    {
        $isClassDeletable = true;

        foreach ($class->getSubClasses() as $subClass) {
            $isClassDeletable = $this->deleteClassRecursively($subClass) && $isClassDeletable;
        }

        return $this->deleteClass($class, $isClassDeletable);
    }

    private function deleteClass(core_kernel_classes_Class $class, bool $isClassDeletable): bool
    {
        return $this->deleteInstances($class->getInstances())
            && $isClassDeletable
            && $this->deleteProperties($class->getProperties())
            && $class->delete();
    }

    /**
     * @param core_kernel_classes_Resource[] $instances
     */
    private function deleteInstances(array $instances): bool
    {
        $status = true;

        foreach ($instances as $instance) {
            if (!$this->permissionChecker->hasWriteAccess($instance->getUri())) {
                $status = false;

                continue;
            }

            $instance->delete();
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
        $indexes = $property->getPropertyValues($this->ontology->getProperty(self::PROPERTY_INDEX));

        if (!$property->delete(true)) {
            return false;
        }

        foreach ($indexes as $indexUri) {
            $this->ontology->getResource($indexUri)->delete(true);
        }

        return true;
    }
}
