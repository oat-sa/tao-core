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
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\OntologyMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\task\migration\service\StatementLastIdRetriever;

class StatementLastIdRetrieverTest extends TestCase
{
    use OntologyMockTrait;

    /** @var @var StatementLastIdRetriever */
    private $subject;

    /** @var core_kernel_persistence_smoothsql_SmoothModel|MockObject */
    private $ontologyMock;

    /** @var common_persistence_SqlPersistence|MockObject */
    private $persistenceMock;

    /** @var common_persistence_sql_Platform|MockObject */
    private $platformMock;

    /** @var QueryBuilder|MockObject */
    private $queryBuilderMock;

    /** @var Statement|MockObject */
    private $statementMock;

    protected function setUp(): void
    {
        $this->subject = new StatementLastIdRetriever();
        $this->ontologyMock = $this->createMock(core_kernel_persistence_smoothsql_SmoothModel::class);
        $this->persistenceMock = $this->createMock(common_persistence_SqlPersistence::class);
        $this->platformMock = $this->createMock(common_persistence_sql_Platform::class);
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);
        $this->statementMock = $this->createMock(Statement::class);
        $this->subject->setModel($this->ontologyMock);
    }

    public function testRetrieve(): void
    {
        $this->ontologyMock
            ->expects($this->once())
            ->method('getPersistence')
            ->willReturn($this->persistenceMock);

        $this->persistenceMock
            ->expects($this->once())
            ->method('getPlatForm')
            ->willReturn($this->platformMock);

        $this->platformMock
            ->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects($this->once())
            ->method('select')
            ->with('MAX(id)')->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects($this->once())
            ->method('from')
            ->with('statements')->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn($this->statementMock);

        $this->statementMock
            ->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        $result = $this->subject->retrieve();
        $this->assertSame(1, $result);
    }
}
