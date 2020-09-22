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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\tasks;

use common_exception_MissingParameter;
use common_report_Report as Report;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\elasticsearch\Exception\FailToUpdatePropertiesException;
use oat\tao\model\search\index\IndexUpdaterInterface;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class UpdateDataAccessControlInIndex implements Action, ServiceLocatorAwareInterface, TaskAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;
    use TaskAwareTrait;
    use LoggerAwareTrait;
    use IndexTrait {
        getParentClasses as getParentClassesOfClass;
    }

    public const READ_ACCESS_PROPERTY = 'read_access';

    /**
     * @param $params
     *
     * @return Report
     *
     * @throws common_exception_MissingParameter
     */
    public function __invoke($params): Report
    {
        if (!is_array($params) || count($params) < 2) {
            throw new common_exception_MissingParameter();
        }

        [$resourceUri, $newPermissions] = $params;

        $resource = $this->getResource($resourceUri);

        $parentClasses = $this->getParentClasses($resource);

        /** @var IndexUpdaterInterface $indexUpdater */
        $indexUpdater = $this->getServiceLocator()->get(IndexUpdaterInterface::SERVICE_ID);

        $type = Report::TYPE_SUCCESS;
        $message = 'Documents in index were successfully updated.';
        $logMessage = 'Data Access Control were being updated by ' . static::class;

        try {
            $indexUpdater->updatePropertyValue($resourceUri, $parentClasses, self::READ_ACCESS_PROPERTY, $newPermissions);
        } catch (FailToUpdatePropertiesException $exception) {
            $type = Report::TYPE_ERROR;
            $message = 'Failed during update search index';
            $logMessage = 'Data Access Control failure: ' . $exception->getMessage();
        }

        $this->logInfo($logMessage);
        return new Report($type, $message);
    }

    /**
     * @param core_kernel_classes_Resource $resource
     *
     * @return array
     */
    private function getParentClasses(core_kernel_classes_Resource $resource): array
    {
        $parentClasses = [];

        if ($resource->isClass()) {
            /** @noinspection PhpParamsInspection */
            $parentClasses = $this->getParentClassesOfClass(
                $this->getClass($resource->getUri())
            );

            return $parentClasses;
        }

        /** @var core_kernel_classes_Class $type */
        foreach ($resource->getTypes() as $type) {
            $parentClasses = array_merge($parentClasses, [$type->getUri()], $this->getParentClassesOfClass($type));
        }
        return $parentClasses;
    }
}
