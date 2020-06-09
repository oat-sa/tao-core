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

namespace unit\scripts\tools\migrations;

use oat\generis\test\TestCase;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Connection;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use Psr\Log\LoggerInterface;
use \common_report_Report as Report;

class AbstractMigrationTest extends TestCase
{
    public function testAddReport()
    {
        $expectedUpReportMessage = 'Migration Up!';
        $expectedDownReportMessage = 'Migration Down!';

        $connectionStub = $this->createMock(Connection::class);
        $loggerStub = $this->createMock(LoggerInterface::class);
        $schemaStub = $this->createMock(Schema::class);

        $upReportStub = $this->createMock(Report::class);
        $upReportStub->method('getMessage')
                     ->willReturn($expectedUpReportMessage);
        $upReportStub->method('getType')
                     ->willReturn(Report::TYPE_INFO);
        $upReportStub->method('getIterator')
                     ->willReturn($this->createMock(\Traversable::class));

        $downReportStub = $this->createMock(Report::class);
        $downReportStub->method('getMessage')
                       ->willReturn($expectedDownReportMessage);
        $downReportStub->method('getType')
                       ->willReturn(Report::TYPE_ERROR);
        $downReportStub->method('getIterator')
                       ->willReturn($this->createMock(\Traversable::class));


        $migration = new class($connectionStub, $loggerStub, $upReportStub, $downReportStub) extends AbstractMigration
        {
            private $upReportStub;
            private $downReportStub;

            public function __construct(Connection $connection, LoggerInterface $logger, Report $upReportStub, Report $downReportStub)
            {
                parent::__construct($connection, $logger);
                $this->upReportStub = $upReportStub;
                $this->downReportStub = $downReportStub;
            }

            public function up(Schema $schema): void
            {
                $this->addReport($this->upReportStub);
            }

            public function down(Schema $schema): void
            {
                $this->addReport($this->downReportStub);
            }
        };

        $expectedUpReportMessage .= PHP_EOL;
        $expectedDownReportMessage .= PHP_EOL;

        $loggerStub->expects($this->exactly(2))
                   ->method('notice')
                   ->withConsecutive(
                       [$this->equalTo($expectedUpReportMessage)],
                       [$this->equalTo($expectedDownReportMessage)]
                   );

        $migration->up($schemaStub);
        $migration->down($schemaStub);
    }
}
