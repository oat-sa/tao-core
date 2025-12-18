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
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\task\migration\service;

use common_report_Report;
use core_kernel_classes_Resource;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\task\migration\MigrationConfig;
use oat\tao\model\task\migration\ResultUnit;
use oat\tao\model\task\migration\ResultUnitCollection;
use oat\tao\model\task\migration\service\QueueMigrationService;
use oat\tao\model\task\migration\service\ResultFilter;
use oat\tao\model\task\migration\service\ResultSearcherInterface;
use oat\tao\model\task\migration\service\ResultUnitProcessorInterface;
use oat\tao\model\task\migration\service\SpawnMigrationConfigServiceInterface;
use oat\tao\model\task\migration\service\StatementLastIdRetriever;

class QueueMigrationServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    private QueueMigrationService $subject;
    private StatementLastIdRetriever|MockObject $statementLastIdRetrieverMock;
    private ResultUnitProcessorInterface|MockObject $resultUnitProcessorMock;
    private ResultSearcherInterface|MockObject $resultSearcherMock;
    private common_report_Report|MockObject $reportMock;
    private ResultFilter|MockObject $resultFilterMock;
    private SpawnMigrationConfigServiceInterface|MockObject $spawnMigrationConfigServiceMock;

    protected function setUp(): void
    {
        $this->resultSearcherMock = $this->createMock(ResultSearcherInterface::class);
        $this->resultUnitProcessorMock = $this->createMock(ResultUnitProcessorInterface::class);
        $this->statementLastIdRetrieverMock = $this->createMock(StatementLastIdRetriever::class);
        $this->reportMock = $this->createMock(common_report_Report::class);
        $this->resultFilterMock = $this->createMock(ResultFilter::class);
        $this->spawnMigrationConfigServiceMock = $this->createMock(SpawnMigrationConfigServiceInterface::class);
        $this->subject = new QueueMigrationService();
        $this->subject->setServiceLocator($this->getServiceManagerMock(
            [
                StatementLastIdRetriever::class => $this->statementLastIdRetrieverMock,
                ResultUnitProcessorInterface::class => $this->resultUnitProcessorMock,
            ]
        ));
    }

    public function testMigrateExecuteOnce(): void
    {
        $config = new MigrationConfig(['start' => 0], 2, 1, false);
        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);
        $resourceResultUnit = new ResultUnit($resourceMock);
        $resourceResultUnitCollection = new ResultUnitCollection($resourceResultUnit, $resourceResultUnit);

        $this->statementLastIdRetrieverMock
            ->method('retrieve')
            ->willReturn(100);

        $this->resultSearcherMock
            ->method('search')
            ->willReturn($resourceResultUnitCollection);

        $this->resultUnitProcessorMock
            ->expects($this->exactly(2))
            ->method('process')
            ->with($resourceResultUnit);

        $this->reportMock
            ->expects($this->once())
            ->method('add');

        $result = $this->subject->migrate(
            $config,
            $this->resultUnitProcessorMock,
            $this->resultSearcherMock,
            $this->resultFilterMock,
            $this->spawnMigrationConfigServiceMock,
            $this->reportMock
        );

        $this->assertNull($result);
    }

    public function testMigrateRespawn(): void
    {
        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);
        $resourceResultUnit = new ResultUnit($resourceMock);
        $resourceResultUnitCollection = new ResultUnitCollection($resourceResultUnit, $resourceResultUnit);

        $this->statementLastIdRetrieverMock
            ->method('retrieve')
            ->willReturn(100);

        $this->resultSearcherMock
            ->method('search')
            ->willReturn($resourceResultUnitCollection);

        $this->resultUnitProcessorMock
            ->expects($this->exactly(2))
            ->method('process')
            ->with($resourceResultUnit);

        $this->reportMock
            ->expects($this->once())
            ->method('add');

        $this->spawnMigrationConfigServiceMock
            ->method('spawn')
            ->willReturn(new MigrationConfig(['start' => 10], 2, 1, true));

        $config = new MigrationConfig(['start' => 0], 2, 1, true);

        $result = $this->subject->migrate(
            $config,
            $this->resultUnitProcessorMock,
            $this->resultSearcherMock,
            $this->resultFilterMock,
            $this->spawnMigrationConfigServiceMock,
            $this->reportMock
        );

        $this->assertInstanceOf(MigrationConfig::class, $result);
        $this->assertSame(10, $result->getCustomParameters()['start']);
        $this->assertSame(1, $result->getPickSize());
        $this->assertSame(2, $result->getChunkSize());
        $this->assertTrue($result->isProcessAll());
    }
}
