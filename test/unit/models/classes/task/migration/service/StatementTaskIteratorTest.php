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

use common_persistence_sql_Platform;
use common_persistence_SqlPersistence;
use core_kernel_persistence_smoothsql_SmoothModel;
use Doctrine\DBAL\Driver\Statement;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\task\migration\service\ResultSearcherService;

class StatementTaskIteratorTest extends TestCase
{
    /**
     * @var ResultSearcherService
     */
    private $subject;

    /**
     * @var common_persistence_SqlPersistence|MockObject
     */
    private $persistenceMock;

    /**
     * @var core_kernel_persistence_smoothsql_SmoothModel|MockObject
     */
    private $ontologyMock;


    /**
     * @var common_persistence_sql_Platform|MockObject
     */
    private $platformMock;

    /**
     * @var Statement|MockObject
     */
    private $statementMock;

    public function setUp(): void
    {
        $this->ontologyMock = $this->createMock(core_kernel_persistence_smoothsql_SmoothModel::class);
        $this->persistenceMock = $this->createMock(common_persistence_SqlPersistence::class);
        $this->platformMock = $this->createMock(common_persistence_sql_Platform::class);
        $this->statementMock = $this->createMock(Statement::class);
        $this->ontologyMock->method('getPersistence')->willReturn($this->persistenceMock);
        $this->subject = new ResultSearcherService();
        $this->subject->setModel($this->ontologyMock);
    }

    public function testGetIterator(): void
    {
        $this->persistenceMock->expects($this->once())->method('getPlatForm')->willReturn($this->platformMock);
        $this->persistenceMock->expects($this->once())->method('query')->willReturn($this->statementMock);
        $this->subject->getIterator([], 0, 1);
    }
}