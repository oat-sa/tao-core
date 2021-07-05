<?php

declare(strict_types=1);

namespace oat\tao\model\task;

use Exception;
use Throwable;
use common_Logger;
use oat\oatbox\action\Action;
use core_kernel_classes_Class;
use oat\oatbox\reporting\Report;
use core_kernel_classes_Resource;
use core_kernel_classes_Property;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\search\index\OntologyIndex;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;

class DeleteClassTask implements Action, TaskAwareInterface
{
    use OntologyAwareTrait;
    use TaskAwareTrait;

    public const PARAM_CLASS_URI = 'classUri';
    public const PARAM_ROOT_CLASS_URI = 'rootClassUri';

    /** @var Report */
    private $report;

    public function __invoke($params): Report
    {
        $this->report = Report::createInfo('Starting class deletion...');

        try {
            $class = $this->getClassFromParams($params, self::PARAM_CLASS_URI);
            $rootClass = $this->getClassFromParams($params, self::PARAM_ROOT_CLASS_URI);

            $label = $class->getLabel();
            $status = $this->deleteClass($class, $rootClass);

            $status
                ? $this->report->add(Report::createSuccess(__('%s has been deleted', $label)))
                : $this->report->add(Report::createError(__('Unable to delete %s', $label)));
        } catch (Throwable $exception) {
            $this->report->add(Report::createError($exception->getMessage()));
        }

        return $this->report;
    }

    private function getClassFromParams(array $parameters, string $name): core_kernel_classes_Class
    {
        $class = $parameters[$name] ?? null;

        if ($class === null) {
            throw new Exception(sprintf('Required parameter %s is missing.', $name));
        }

        if (!is_string($class)) {
            throw new Exception('Provided value should be a string');
        }

        return $this->getClass($class);
    }

    private function deleteClass(core_kernel_classes_Class $class, core_kernel_classes_Class $rootClass): bool
    {
        if ($class->isSubClassOf($rootClass) && ! $class->equals($rootClass)) {
            $returnValue = true;

            foreach ($class->getInstances() as $instance) {
                $instance->delete();
            }

            foreach ($class->getSubClasses(false) as $subclass) {
                $returnValue = $returnValue && $this->deleteClass($subclass, $rootClass);
            }

            foreach ($class->getProperties() as $classProperty) {
                $returnValue = $returnValue && $this->deleteClassProperty($classProperty);
            }

            return $returnValue && $class->delete();
        } else {
            $message = sprintf(
                'Tried to delete class %s as if it were a subclass of %s',
                $class->getUri(),
                $rootClass->getUri()
            );

            common_Logger::w($message);
            $this->report->add(Report::createWarning($message));
        }

        return false;
    }

    private function deleteClassProperty(core_kernel_classes_Property $property): bool
    {
        $indexes = $property->getPropertyValues(new core_kernel_classes_Property(OntologyIndex::PROPERTY_INDEX));

        // Delete property and the existing values of this property
        if ($returnValue = $property->delete(true)) {
            // Delete index linked to the property
            foreach ($indexes as $indexUri) {
                $index = new core_kernel_classes_Resource($indexUri);
                $returnValue = $index->delete(true);
            }
        }

        return $returnValue;
    }
}
