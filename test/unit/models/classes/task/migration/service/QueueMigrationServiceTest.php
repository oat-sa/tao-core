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


namespace oat\tao\test\unit\models\classes\task\migration\service;


use common_report_Report;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\task\migration\MigrationConfig;
use oat\tao\model\task\migration\ResourceResultUnit;
use oat\tao\model\task\migration\ResultUnitCollection;
use oat\tao\model\task\migration\service\QueueMigrationService;
use oat\tao\model\task\migration\service\ResultSearcherInterface;
use oat\tao\model\task\migration\service\ResultUnitProcessorInterface;
use oat\tao\model\task\migration\service\StatementLastIdRetriever;

class QueueMigrationServiceTest extends TestCase
{
    /** @var QueueMigrationService */
    private $subject;

    /** @var StatementLastIdRetriever|MockObject */
    private $statementLastIdRetrieverMock;

    /** @var ResultUnitProcessorInterface|MockObject */
    private $resultUnitProcessorMock;

    /** @var ResultSearcherInterface|MockObject */
    private $resultSearcherMock;

    /** @var common_report_Report|MockObject */
    private $reportMock;

    public function setUp(): void
    {
        $this->resultSearcherMock = $this->createMock(ResultSearcherInterface::class);
        $this->resultUnitProcessorMock = $this->createMock(ResultUnitProcessorInterface::class);
        $this->statementLastIdRetrieverMock = $this->createMock(StatementLastIdRetriever::class);
        $this->reportMock = $this->createMock(common_report_Report::class);
        $this->subject = new QueueMigrationService();
        $this->subject->setServiceLocator($this->getServiceLocatorMock([
            StatementLastIdRetriever::class => $this->statementLastIdRetrieverMock,
            ResultUnitProcessorInterface::class => $this->resultUnitProcessorMock,
        ]));
    }

    public function testMigrate()
    {
        $config = new MigrationConfig(0, 2, 1, false);
        $resourceMock = $this->createMock(\core_kernel_classes_Resource::class);
        $resourceResultUnit = new ResourceResultUnit($resourceMock);
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

        $result = $this->subject->migrate($config, $this->resultUnitProcessorMock, $this->resultSearcherMock, $this->reportMock);

        $this->assertNull($result);
    }

}
