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
 * Copyright (c) 2020-2023 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

use oat\generis\model\data\Ontology;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\log\LoggerService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\event\DataAccessControlChangedEvent;
use oat\tao\model\listener\DataAccessControlChangedListener;
use oat\tao\model\search\tasks\UpdateDataAccessControlInIndex;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use Psr\Log\LoggerInterface;

class DataAccessControlChangedListenerTest extends TestCase
{
    /** @var QueueDispatcherInterface|MockObject */
    private $queueDispatcher;

    /** @var DataAccessControlChangedListener/ */
    private $sut;

    /** @var MockObject|LoggerInterface */
    private $logger;

    /** @var MockObject|Ontology */
    private $ontology;
    private $advancedSearchChecker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new DataAccessControlChangedListener();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->ontology = $this->createMock(Ontology::class);

        $this->queueDispatcher = $this->createMock(QueueDispatcherInterface::class);
        $this->advancedSearchChecker = $this->createMock(AdvancedSearchChecker::class);

        $this->sut->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    QueueDispatcherInterface::SERVICE_ID => $this->queueDispatcher,
                    LoggerService::SERVICE_ID => $this->logger,
                    Ontology::SERVICE_ID => $this->ontology,
                    AdvancedSearchChecker::class => $this->advancedSearchChecker,
                ]
            )
        );
    }

    /**
     * @dataProvider handleEventSuccessfulCasesDataProvider
     */
    public function testHandleEventShouldCreateTaskSuccessfully(bool $isRecursive): void
    {
        $documentUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';

        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $this->advancedSearchChecker
            ->method('isEnabled')
            ->willReturn(true);

        $resource
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($documentUri);
        $resource
            ->method('getLabel')
            ->willReturn('Resource Label');

        $this->logger
            ->expects($this->exactly(2))
            ->method('debug')
            ->withConsecutive(
                ['triggering index update on DataAccessControlChanged event'],
                [
                    $this->stringStartsWith(
                        'Dispatching UpdateDataAccessControlInIndex for root resource'
                    )
                ]
            );

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->willReturn($resource);

        $this->queueDispatcher
            ->expects($this->once())
            ->method('createTask')
            ->with(
                new UpdateDataAccessControlInIndex(),
                [
                    $documentUri,
                    []
                ],
                'Adding/updating search index for updated resource',
                null,
                false
            );

        $this->sut->handleEvent(
            new DataAccessControlChangedEvent(
                $documentUri,
                [],
                $isRecursive,
                $isRecursive,
                $documentUri
            )
        );
    }

    public function handleEventSuccessfulCasesDataProvider(): array
    {
        return [
            'case event is recursive and resource is NOT a class' => [
                true,
            ],
            'case event is recursive and resource is a class' => [
                true,
            ],
            'case event is NOT recursive and resource is not a class' => [
                false,
            ],
        ];
    }

    public function testHandleEventShouldNotCreateTasks(): void
    {
        $documentUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $this->advancedSearchChecker->method('isEnabled')->willReturn(true);

        $resource->expects($this->never())->method('getUri');

        $this->logger->expects($this->once())->method('debug')
            ->with('triggering index update on DataAccessControlChanged event');

        $this->ontology->expects($this->once())->method('getResource')
            ->willReturn($resource);

        $this->queueDispatcher->expects($this->never())
            ->method('createTask');

        $this->sut->handleEvent(new DataAccessControlChangedEvent($documentUri, [], false));
    }
}
