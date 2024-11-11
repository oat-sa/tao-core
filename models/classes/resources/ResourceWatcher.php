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
 * Copyright (c) 2017-2022 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\resources;

use common_http_Request;
use core_kernel_classes_Class;
use core_kernel_classes_Literal;
use core_kernel_classes_Resource;
use oat\generis\model\data\event\ResourceCreated;
use oat\generis\model\data\event\ResourceDeleted;
use oat\generis\model\data\event\ResourceUpdated;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\search\index\IndexUpdaterInterface;
use oat\tao\model\search\Search;
use oat\tao\model\search\tasks\UpdateClassInIndex;
use oat\tao\model\search\tasks\UpdateResourceInIndex;
use oat\tao\model\search\tasks\UpdateTestResourceInIndex;
use oat\tao\model\TaoOntology;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\Translation\Service\TranslationDeletionService;

/**
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class ResourceWatcher extends ConfigurableService
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'tao/ResourceWatcher';

    /** Time in seconds for updatedAt threshold */
    public const OPTION_THRESHOLD = 'threshold';

    /** @var array */
    protected $updatedAtCache = [];

    public function catchCreatedResourceEvent(ResourceCreated $event): void
    {
        $resource = $event->getResource();
        $property = $this->getProperty(TaoOntology::PROPERTY_UPDATED_AT);
        $now = microtime(true);
        $this->updatedAtCache = [];
        $this->updatedAtCache[$resource->getUri()] = $now;
        $resource->editPropertyValues($property, $now);

        $this->getLogger()->debug('triggering index update on resourceCreated event');

        $taskMessage = __('Adding search index for created resource');
        $this->createResourceIndexingTask($resource, $taskMessage);
    }

    /**
     * @throws \core_kernel_persistence_Exception
     */
    public function catchUpdatedResourceEvent(ResourceUpdated $event): void
    {
        $resource = $event->getResource();
        $updatedAt = $this->getUpdatedAt($resource);

        if ($updatedAt instanceof core_kernel_classes_Literal) {
            $updatedAt = (int) $updatedAt->literal;
        }

        $now = microtime(true);
        $threshold = $this->getOption(self::OPTION_THRESHOLD);

        if ($updatedAt === null || ($now - $updatedAt) > $threshold) {
            $this->getLogger()->debug(
                'triggering index update on resourceUpdated event'
            );

            $property = $this->getProperty(TaoOntology::PROPERTY_UPDATED_AT);
            $this->updatedAtCache[$resource->getUri()] = $now;
            $resource->editPropertyValues($property, $now);

            $taskMessage = __('Adding/updating search index for updated resource');
            $this->createResourceIndexingTask($resource, $taskMessage);
        }
    }

    public function catchDeletedResourceEvent(ResourceDeleted $event): void
    {
        try {
            $this->getSearch()->remove($event->getId());

            if ($this->getFeatureFlagChecker()->isEnabled('FEATURE_FLAG_TRANSLATION_ENABLED')) {
                $this->getTranslationDeletionService()->deleteByOriginResourceUri($event->getId());
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->getLogger()->error(
                sprintf("Error delete index document for %s with message %s", $event->getId(), $message)
            );
        }
    }

     /**
     * @return \core_kernel_classes_Container
     * @throws \core_kernel_persistence_Exception
     */
    public function getUpdatedAt(core_kernel_classes_Resource $resource)
    {
        if (isset($this->updatedAtCache[$resource->getUri()])) {
            $updatedAt = $this->updatedAtCache[$resource->getUri()];
        } else {
            $property = $this->getProperty(TaoOntology::PROPERTY_UPDATED_AT);
            $updatedAt = $resource->getOnePropertyValue($property);
            if ($updatedAt && $updatedAt instanceof core_kernel_classes_Literal) {
                $updatedAt = (int) $updatedAt->literal;
            }
            $this->updatedAtCache[$resource->getUri()] = $updatedAt;
        }
        return $updatedAt;
    }

    /**
     * Creates a task in the task queue to index/re-index created/updated resource
     */
    private function createResourceIndexingTask(core_kernel_classes_Resource $resource, string $message): void
    {
        if ($this->getServiceManager()->get(AdvancedSearchChecker::class)->isEnabled()) {
            /** @var QueueDispatcherInterface $queueDispatcher */
            $queueDispatcher = $this->getServiceManager()->get(QueueDispatcherInterface::SERVICE_ID);

            if ($this->hasClassSupport($resource) && !$this->ignoreEditIemClassUpdates()) {
                $queueDispatcher->createTask(new UpdateClassInIndex(), [$resource->getUri()], $message);
                return;
            }

            $rootIndexClass = $this->getResourceIndexType($resource);
            if ($rootIndexClass) {
                $isTest = TaoOntology::CLASS_URI_TEST === $rootIndexClass;
                $queueDispatcher->createTask(
                    $isTest ? new UpdateTestResourceInIndex() : new UpdateResourceInIndex(),
                    [$resource->getUri()],
                    $message
                );
            }
        }
    }

    /**
     * This method actually finds a root class that is supported by used tao/IndexUpdater
     */
    private function getResourceIndexType(core_kernel_classes_Resource $resource): ?string
    {
        $resourceTypeIds = $this->getResourceTypes($resource);
        $checkedResourceTypes = [OntologyRdfs::RDFS_RESOURCE, TaoOntology::CLASS_URI_OBJECT];
        $resourceTypeIds = array_diff($resourceTypeIds, [OntologyRdfs::RDFS_RESOURCE, TaoOntology::CLASS_URI_OBJECT]);

        while (!empty($resourceTypeIds)) {
            $classUri = array_pop($resourceTypeIds);
            $hasClassSupport = $this->getServiceManager()
                ->get(IndexUpdaterInterface::SERVICE_ID)
                ->hasClassSupport(
                    $classUri
                );

            if ($hasClassSupport) {
                return $classUri;
            }

            $class = $this->getClass($classUri);

            foreach ($class->getParentClasses() as $parent) {
                if (!in_array($parent->getUri(), $checkedResourceTypes)) {
                    $resourceTypeIds[] = $parent->getUri();
                }
            }
            $checkedResourceTypes[] = $class->getUri();
        }

        return null;
    }

    private function getResourceTypes(core_kernel_classes_Resource $resource): array
    {
        return array_map(
            function (core_kernel_classes_Class $resourceType): string {
                return $resourceType->getUri();
            },
            $resource->getTypes()
        );
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

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getServiceManager()->getContainer()->get(FeatureFlagChecker::class);
    }

    private function getTranslationDeletionService(): TranslationDeletionService
    {
        return $this->getServiceManager()->getContainer()->get(TranslationDeletionService::class);
    }

    private function getSearch(): Search
    {
        return $this->getServiceManager()->getContainer()->get(Search::SERVICE_ID);
    }
}
