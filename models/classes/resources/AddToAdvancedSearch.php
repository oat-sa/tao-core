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
 * Copyright (c) 2017-2021 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\resources;

use common_http_Request;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\data\event\ResourceCreated;
use oat\generis\model\data\event\ResourceDeleted;
use oat\generis\model\data\event\ResourceUpdated;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\search\index\IndexUpdaterInterface;
use oat\tao\model\search\Search;
use oat\tao\model\search\tasks\UpdateClassInIndex;
use oat\tao\model\search\tasks\UpdateResourceInIndex;
use oat\tao\model\TaoOntology;
use oat\tao\model\taskQueue\QueueDispatcherInterface;

/**
 * @TODO FIXME TMP Class
 */
class AddToAdvancedSearch extends ConfigurableService // Remove me after refactoring
{
    use OntologyAwareTrait;

    /**
     * @TODO Move this logic to AdvancedSearch when handling all the events the ResourceWatcher does
     */
    private function createResourceIndexingTask(core_kernel_classes_Resource $resource, string $message): void
    {
        //@TODO FIXME make sure it only indexes if AdvancedSearch is ENABLED

        /** @var QueueDispatcherInterface $queueDispatcher */
        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);

        if ($this->hasClassSupport($resource) && !$this->ignoreEditIemClassUpdates()) {
            $queueDispatcher->createTask(new UpdateClassInIndex(), [$resource->getUri()], $message);
            return;
        }

        if ($this->hasResourceSupport($resource)) {
            $queueDispatcher->createTask(new UpdateResourceInIndex(), [$resource->getUri()], $message);
        }
    }

    private function hasResourceSupport(core_kernel_classes_Resource $resource): bool
    {
        $resourceTypeIds = array_map(
            function (core_kernel_classes_Class $resourceType): string {
                return $resourceType->getUri();
            },
            $resource->getTypes()
        );

        $checkedResourceTypes = [OntologyRdfs::RDFS_RESOURCE, TaoOntology::CLASS_URI_OBJECT];
        $resourceTypeIds = array_diff($resourceTypeIds, [OntologyRdfs::RDFS_RESOURCE, TaoOntology::CLASS_URI_OBJECT]);

        while (!empty($resourceTypeIds)) {
            $classUri = array_pop($resourceTypeIds);

            $hasClassSupport = $this->getServiceLocator()
                ->get(IndexUpdaterInterface::SERVICE_ID)
                ->hasClassSupport(
                    $classUri
                );

            if ($hasClassSupport) {
                return true;
            }

            $class = $this->getClass($classUri);

            foreach ($class->getParentClasses() as $parent) {
                if (!in_array($parent->getUri(), $checkedResourceTypes)) {
                    $resourceTypeIds[] = $parent->getUri();
                }
            }
            $checkedResourceTypes[] = $class->getUri();
        }

        return false;
    }

    private function hasClassSupport(core_kernel_classes_Resource $resource): bool
    {
        return $resource instanceof core_kernel_classes_Class;
    }

    private function ignoreEditIemClassUpdates(): bool
    {
        try {
            $url = parse_url(common_http_Request::currentRequest()->getUrl());
        } catch (\common_exception_Error $e) {
            return false;
        }

        return isset($url['path']) && $url['path'] === '/taoItems/Items/editItemClass';
    }
}
