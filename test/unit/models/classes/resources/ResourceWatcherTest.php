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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\resources;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use Exception;
use oat\generis\model\data\event\ResourceCreated;
use oat\generis\model\data\event\ResourceDeleted;
use oat\generis\model\data\event\ResourceUpdated;
use oat\generis\model\data\Ontology;
use oat\generis\test\TestCase;
use oat\oatbox\log\LoggerService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\resources\ResourceWatcher;
use oat\tao\model\search\index\IndexUpdaterInterface;
use oat\tao\model\search\Search;
use oat\tao\model\search\tasks\UpdateClassInIndex;
use oat\tao\model\search\tasks\UpdateResourceInIndex;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use Psr\Log\LoggerInterface;

class ResourceWatcherTest extends TestCase
{
    /** @var ResourceWatcher|MockObject */
    private $sut;

    /** @var IndexUpdaterInterface|MockObject */
    private $indexUpdater;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /**  @var Ontology|MockObject */
    private $ontology;

    /** @var QueueDispatcherInterface|MockObject */
    private $queueDispatcher;

    /** @var core_kernel_classes_Resource|MockObject */
    private $resource;

    /** @var MockObject|Search */
    private $search;
    /**
     * @var AdvancedSearchChecker|MockObject
     */
    private $advancedSearchChecker;

    protected function setUp(): void
    {
        $this->sut = new ResourceWatcher();

        $this->indexUpdater = $this->createMock(IndexUpdaterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->ontology = $this->createMock(Ontology::class);
        $this->queueDispatcher = $this->createMock(QueueDispatcherInterface::class);
        $this->resource = $this->createMock(core_kernel_classes_Resource::class);
        $this->search = $this->createMock(Search::class);
        $this->advancedSearchChecker = $this->createMock(AdvancedSearchChecker::class);

        $serviceLocator = $this->getServiceLocatorMock(
            [
                IndexUpdaterInterface::SERVICE_ID => $this->indexUpdater,
                QueueDispatcherInterface::SERVICE_ID => $this->queueDispatcher,
                Ontology::SERVICE_ID => $this->ontology,
                LoggerService::SERVICE_ID => $this->logger,
                Search::SERVICE_ID => $this->search,
                AdvancedSearchChecker::class => $this->advancedSearchChecker,
            ]
        );
        $this->sut->setServiceLocator($serviceLocator);
    }

    public function testCatchCreatedResourceEvent_mustCreateIndexTaskInCaseResourceIsSupportedByIndex(): void
    {
        $classUri = 'https://tao.docker.localhost/ontologies/tao.rdf#Item';
        $this->mockHasClassSupportIndexUpdater($classUri);
        $this->mockAdvancedSearchEnabled(true);

        $this->mockGetTypesResource($classUri);

        $resourceUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';
        $this->mockCreateTaskQueueDispatcher(
            $resourceUri,
            'Adding search index for created resource',
            new UpdateResourceInIndex()
        );

        $this->mockGetPropertyOntology($this->once());

        $this->mockGetUriResource($resourceUri);

        $this->mockDebugLogger('triggering index update on resourceCreated event');

        $this->sut->catchCreatedResourceEvent(
            new ResourceCreated($this->resource)
        );
    }

    public function testCatchCreatedResourceEvent_mustCreateIndexTaskInCaseResourceIsSupportedByIndexWhenRootClassBelongsToParent(): void
    {
        $classUri = 'https://tao.docker.localhost/ontologies/tao.rdf#Item';
        $this->indexUpdater->expects($this->at(0))
            ->method('hasClassSupport')
            ->with($classUri)
            ->willReturn(
                false
            );
        $this->indexUpdater->expects($this->at(1))
            ->method('hasClassSupport')
            ->with($classUri)
            ->willReturn(
                true
            );
        $this->mockAdvancedSearchEnabled(true);

        $this->mockGetTypesResource($classUri);

        $resourceUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';
        $this->mockCreateTaskQueueDispatcher(
            $resourceUri,
            'Adding search index for created resource',
            new UpdateResourceInIndex()
        );

        $this->mockGetPropertyOntology($this->once());

        $parentClass = $this->createMock(
            core_kernel_classes_Class::class
        );

        $class = $this->createMock(
            core_kernel_classes_Class::class
        );

        $parentClass->expects($this->any())->method('getUri')->willReturn(
            $classUri
        );

        $class->expects($this->once())->method('getParentClasses')->willReturn(
            [
                $parentClass
            ]
        );
        $this->ontology->expects($this->once())
            ->method('getClass')
            ->willReturn(
                $class
            );

        $this->mockGetUriResource($resourceUri);

        $this->mockDebugLogger('triggering index update on resourceCreated event');

        $this->sut->catchCreatedResourceEvent(
            new ResourceCreated($this->resource)
        );
    }

    public function testCatchCreatedResourceEvent_mustNotCreateIndexTaskInCaseResourceIsNotSupported(): void
    {
        $classUri = 'https://tao.docker.localhost/ontologies/tao.rdf#Item';
        $this->indexUpdater->expects($this->any())
            ->method('hasClassSupport')
            ->with($classUri)
            ->willReturn(false);
        $this->mockAdvancedSearchEnabled(true);

        $this->mockGetTypesResource($classUri);

        $resourceUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';
        $this->queueDispatcher->expects($this->never())->method('createTask');

        $this->mockGetPropertyOntology($this->once());

        $class = $this->createMock(
            core_kernel_classes_Class::class
        );

        $class->expects($this->once())->method('getParentClasses')
            ->willReturn([]);

        $this->ontology->expects($this->once())
            ->method('getClass')
            ->willReturn($class);

        $this->mockGetUriResource($resourceUri);

        $this->mockDebugLogger('triggering index update on resourceCreated event');

        $this->sut->catchCreatedResourceEvent(
            new ResourceCreated($this->resource)
        );
    }

    public function testCatchUpdatedResourceEvent_mustCreateIndexTaskInCaseResourceIsSupportedByIndex(): void
    {
        $classUri = 'https://tao.docker.localhost/ontologies/tao.rdf#Item';
        $this->mockHasClassSupportIndexUpdater($classUri);
        $this->mockAdvancedSearchEnabled(true);

        $this->mockGetTypesResource($classUri);

        $resourceUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';
        $this->mockCreateTaskQueueDispatcher(
            $resourceUri,
            'Adding/updating search index for updated resource',
            new UpdateResourceInIndex()
        );

        $this->mockGetPropertyOntology($this->atLeast(2));

        $this->mockGetUriResource($resourceUri);

        $this->mockDebugLogger('triggering index update on resourceUpdated event');

        $this->resource->expects($this->once())->method('editPropertyValues');

        $this->sut->catchUpdatedResourceEvent(
            new ResourceUpdated($this->resource)
        );
    }

    public function testCatchUpdatedResourceEvent_mustNotCreateIndexTask(): void
    {
        $advancedSearchEnabled = false;

        $this->mockAdvancedSearchEnabled($advancedSearchEnabled);

        $resourceUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';
        $this->mockCreateTaskQueueDispatcher(
            $resourceUri,
            'Adding/updating search index for updated resource',
            new UpdateResourceInIndex(),
            $advancedSearchEnabled
        );

        $this->mockGetPropertyOntology($this->atLeast(2));

        $this->mockGetUriResource($resourceUri);

        $this->mockDebugLogger('triggering index update on resourceUpdated event');

        $this->sut->catchUpdatedResourceEvent(
            new ResourceUpdated($this->resource)
        );
    }

    public function testCatchUpdatedResourceEvent_mustCreateIndexTaskInCaseClassIsSupportedByIndex(): void
    {
        $resourceUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';
        $this->mockAdvancedSearchEnabled(true);

        $this->mockCreateTaskQueueDispatcher(
            $resourceUri,
            'Adding/updating search index for updated resource',
            new UpdateClassInIndex()
        );

        $this->mockGetPropertyOntology($this->atLeast(2));

        $this->resource = $this->createMock(core_kernel_classes_Class::class);
        $this->resource->expects($this->any())
            ->method('getUri')
            ->willReturn($resourceUri);

        $this->mockDebugLogger('triggering index update on resourceUpdated event');

        $this->resource->expects($this->once())->method('editPropertyValues');

        $this->sut->catchUpdatedResourceEvent(
            new ResourceUpdated($this->resource)
        );
    }

    public function testCatchDeletedResourceEventSuccess(): void
    {
        $resourceUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';

        $this->search->expects($this->once())->method('remove');

        $this->sut->catchDeletedResourceEvent(
            new ResourceDeleted($resourceUri)
        );
    }

    public function testCatchDeletedResourceEventFail(): void
    {
        $resourceUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';

        $this->search->expects($this->once())
            ->method('remove')
            ->willThrowException(new Exception());
        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Error delete index document for https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec with message '
            );

        $this->sut->catchDeletedResourceEvent(
            new ResourceDeleted($resourceUri)
        );
    }

    private function mockGetTypesResource(string $classUri): void
    {
        $class = $this->createMock(core_kernel_classes_Class::class);
        $class->expects($this->once())
            ->method('getUri')
            ->willReturn($classUri);

        $this->resource->expects($this->once())
            ->method('getTypes')
            ->willReturn(
                [
                    $class
                ]
            );
    }

    private function mockHasClassSupportIndexUpdater(string $classUri): void
    {
        $this->indexUpdater->expects($this->once())
            ->method('hasClassSupport')
            ->with($classUri)
            ->willReturn(
                true
            );
    }

    private function mockAdvancedSearchEnabled(bool $enabled)
    {
        $this->advancedSearchChecker->expects($this->once())->method('isEnabled')->willReturn($enabled);
    }

    private function mockCreateTaskQueueDispatcher(
        string $resourceUri,
        string $taskMessage,
        TaskAwareInterface $task,
        bool $expectEnqueue = true
    ): void {
        $this->queueDispatcher->expects($expectEnqueue ? $this->once() : $this->never())
            ->method('createTask')
            ->with(
                $task,
                [$resourceUri],
                $taskMessage,
                null,
                false
            );
    }

    private function mockGetPropertyOntology(InvocationOrder $expectation): void
    {
        $this->ontology->expects($expectation)->method('getProperty')->willReturn(
            $this->createMock(core_kernel_classes_Property::class)
        );
    }

    private function mockGetUriResource(string $resourceUri): void
    {
        $this->resource->expects($this->any())
            ->method('getUri')
            ->willReturn($resourceUri);
    }

    private function mockDebugLogger(string $message): void
    {
        $this->logger->expects($this->once())
            ->method('debug')
            ->with($message);
    }
}
