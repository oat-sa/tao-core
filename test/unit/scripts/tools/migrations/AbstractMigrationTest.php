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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\scripts\tools\migrations;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Connection;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use Psr\Log\LoggerInterface;
use common_report_Report as Report;

class AbstractMigrationTest extends TestCase
{
    public function testAddReport()
    {
        $expectedUpReportMessage = 'Migration Up!';
        $expectedDownReportMessage = 'Migration Down!';

        $connectionMock = $this->createMock(Connection::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $schemaMock = $this->createMock(Schema::class);

        $upReportMock = $this->createMock(Report::class);
        $upReportMock->method('getMessage')
                     ->willReturn($expectedUpReportMessage);
        $upReportMock->method('getType')
                     ->willReturn(Report::TYPE_INFO);
        $upReportMock->method('getIterator')
            ->willReturn($this->createMock(\ArrayIterator::class));

        $downReportMock = $this->createMock(Report::class);
        $downReportMock->method('getMessage')
                       ->willReturn($expectedDownReportMessage);
        $downReportMock->method('getType')
                       ->willReturn(Report::TYPE_ERROR);
        $downReportMock->method('getIterator')
            ->willReturn($this->createMock(\ArrayIterator::class));


        $migration = new class (
            $connectionMock,
            $loggerMock,
            $upReportMock,
            $downReportMock
        ) extends AbstractMigration {
            private $upReportMock;
            private $downReportMock;

            public function __construct(
                Connection $connection,
                LoggerInterface $logger,
                Report $upReportMock,
                Report $downReportMock
            ) {
                parent::__construct($connection, $logger);
                $this->upReportMock = $upReportMock;
                $this->downReportMock = $downReportMock;
            }

            public function up(Schema $schema): void
            {
                $this->addReport($this->upReportMock);
            }

            public function down(Schema $schema): void
            {
                $this->addReport($this->downReportMock);
            }
        };

        $expectedUpReportMessage .= PHP_EOL;
        $expectedDownReportMessage .= PHP_EOL;

        $loggerMock->expects($this->exactly(2))
                   ->method('notice')
                   ->withConsecutive(
                       [$this->equalTo($expectedUpReportMessage)],
                       [$this->equalTo($expectedDownReportMessage)]
                   );

        $migration->up($schemaMock);
        $migration->down($schemaMock);
    }
}
