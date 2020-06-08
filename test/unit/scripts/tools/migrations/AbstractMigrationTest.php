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

class AbstractMigrationTest extends TestCase
{
    public function testAddReport()
    {
        $connectionMock = $this->createMock(Connection::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $migration = new class($connectionMock, $loggerMock) extends AbstractMigration
        {
            public function up(Schema $schema) : void
            {
                $this->addReport(\common_report_Report::createInfo('Migration Up!'));
            }
        };

        $expectedReportMessage = "Migration Up!";

        // Reports will be colored only if it is not MS Windows running AND
        // the 'TAO_CONSOLE' environment variable is not set to 'nocolor'.
        // Let's make this test resilient to that...
        if (getenv('TAO_CONSOLE') !== 'nocolor' && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $expectedReportMessage = "\033[0;37m${expectedReportMessage}\033[0m" . PHP_EOL;
        }

        $loggerMock->expects($this->once())
                   ->method('notice')
                   ->with($this->equalTo($expectedReportMessage));

        $migration->up($this->createMock(Schema::class));
    }
}