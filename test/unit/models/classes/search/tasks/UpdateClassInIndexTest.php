<?php

/** @noinspection PhpUndefinedClassInspection */

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
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\search\tasks;

use common_exception_MissingParameter;
use common_report_Report as Report;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\log\LoggerService;
use oat\tao\model\search\index\IndexIterator;
use oat\tao\model\search\index\IndexIteratorFactory;
use oat\tao\model\search\Search;
use oat\tao\model\search\tasks\UpdateClassInIndex;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class UpdateClassInIndexTest extends TestCase
{
    use ServiceManagerMockTrait;

    private UpdateClassInIndex $sut;
    private IndexIteratorFactory|MockObject $indexIterator;
    private Search|MockObject $search;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        $this->indexIterator = $this->createMock(IndexIteratorFactory::class);

        $this->sut = new UpdateClassInIndex(
            $this->indexIterator
        );
        $this->search = $this->createMock(Search::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $serviceLocator = $this->getServiceManagerMock(
            [
                Search::SERVICE_ID => $this->search,
                LoggerService::SERVICE_ID => $this->logger
            ]
        );

        $this->sut->setServiceLocator($serviceLocator);
    }

    public function testTaskThereIsNoResourcesToIndex(): void
    {
        $indexIterator = $this->createMock(IndexIterator::class);

        $this->indexIterator->expects($this->once())
            ->method('create')
            ->willReturn(
                $indexIterator
            );

        $this->search->expects($this->once())
            ->method('index')
            ->with($indexIterator)
            ->willReturn(0);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('0 resources have been indexed by oat\tao\model\search\tasks\UpdateClassInIndex');

        $report = $this->sut->__invoke(
            ['https://tao.docker.localhost/ontologies/tao.rdf#i5f478159bc6a0794b51ee1a7f8cf0a4c']
        );

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals('Zero documents were added/updated in index.', $report->getMessage());
        $this->assertEquals(Report::TYPE_INFO, $report->getType());
    }

    public function testTaskThereIsResourcesToIndex(): void
    {
        $indexIterator = $this->createMock(IndexIterator::class);

        $this->indexIterator->expects($this->once())
            ->method('create')
            ->willReturn(
                $indexIterator
            );

        $this->search->expects($this->once())
            ->method('index')
            ->with($indexIterator)
            ->willReturn(5);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('5 resources have been indexed by oat\tao\model\search\tasks\UpdateClassInIndex');

        $report = $this->sut->__invoke(
            ['https://tao.docker.localhost/ontologies/tao.rdf#i5f478159bc6a0794b51ee1a7f8cf0a4c']
        );

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals('Documents in index were successfully updated.', $report->getMessage());
        $this->assertEquals(Report::TYPE_SUCCESS, $report->getType());
    }

    /**
     * @dataProvider provideInvalidParameters
     *
     * @param mixed $parameter
     *
     * @throws common_exception_MissingParameter
     *
     */
    public function testTaskInvalidInvokableParameters($parameter): void
    {
        $this->expectException(common_exception_MissingParameter::class);

        $this->sut->__invoke(
            $parameter
        );
    }

    public function provideInvalidParameters(): array
    {
        return [
            'Empty Array' => [
                []
            ],
            'String' => [
                ''
            ],
            'Null' => [
                null
            ],
        ];
    }
}
