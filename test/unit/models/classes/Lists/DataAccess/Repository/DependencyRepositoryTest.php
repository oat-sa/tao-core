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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\DataAccess\Repository;

use common_persistence_sql_Platform;
use common_persistence_SqlPersistence;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use oat\generis\persistence\PersistenceManager;
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Domain\Dependency;
use oat\tao\model\Lists\Business\Domain\DependencyCollection;
use oat\tao\model\Lists\DataAccess\Repository\DependencyRepository;
use PHPUnit\Framework\MockObject\MockObject;

class DependencyRepositoryTest extends TestCase
{
    /** @var DependencyRepository */
    private $sut;

    /** @var QueryBuilder|MockObject */
    private $queryBuilder;

    public function setUp(): void
    {
        $this->queryBuilder = $this->createMock(QueryBuilder::class);

        $platform = $this->createMock(common_persistence_sql_Platform::class);
        $platform
            ->method('getQueryBuilder')
            ->willReturn($this->queryBuilder);

        $persistence = $this->createMock(common_persistence_SqlPersistence::class);
        $persistence
            ->method('getPlatform')
            ->willReturn($platform);

        $persistenceManager = $this->createMock(PersistenceManager::class);
        $persistenceManager
            ->method('getPersistenceById')
            ->willReturn($persistence);

        $this->sut = new DependencyRepository($persistenceManager);
    }

    public function testFindAll(): void
    {
        $collection = new DependencyCollection();
        $collection->append(new Dependency('uri'));

        $statement = $this->createMock(Statement::class);
        $statement->method('fetchAll')
            ->willReturn(
                [
                    'uri'
                ]
            );

        $expressionBuilder = $this->createMock(ExpressionBuilder::class);

        $this->queryBuilder
            ->method('expr')
            ->willReturn($expressionBuilder);
        $this->queryBuilder
            ->method('select')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('from')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('innerJoin')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('andWhere')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('setParameter')
            ->willReturnSelf();

        $this->queryBuilder->method('execute')
            ->willReturn($statement);

        $this->assertEquals(
            $collection,
            $this->sut->findAll(
                [
                    'listUri' => 'uri1'
                ]
            )
        );
    }

    public function testFindChildListIds(): void
    {
        $expectedChildIds = ['uri'];
        $collection = new DependencyCollection();
        $collection->append(new Dependency('uri'));

        $statement = $this->createMock(Statement::class);
        $statement->method('fetchAll')
            ->willReturn($expectedChildIds);

        $expressionBuilder = $this->createMock(ExpressionBuilder::class);

        $this->queryBuilder
            ->method('expr')
            ->willReturn($expressionBuilder);
        $this->queryBuilder
            ->method('select')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('from')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('innerJoin')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('andWhere')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('setParameter')
            ->willReturnSelf();

        $this->queryBuilder->method('execute')
            ->willReturn($statement);

        $this->assertSame(
            $expectedChildIds,
            $this->sut->findChildListIds(
                [
                    'parentListUris' => ['uri1'],
                    'parentListValues' => ['value1'],
                ]
            )
        );
    }

    public function testFindChildListUris(): void
    {
        $childUriList = ['uri'];

        $expressionBuilder = $this->createMock(ExpressionBuilder::class);

        $statement = $this->createMock(Statement::class);
        $statement->method('fetchAll')
            ->willReturn($childUriList);

        $this->queryBuilder
            ->method('expr')
            ->willReturn($expressionBuilder);
        $this->queryBuilder
            ->method('select')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('from')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('where')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('innerJoin')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('andWhere')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('setParameter')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('groupBy')
            ->willReturnSelf();

        $this->queryBuilder->method('execute')
            ->willReturn($statement);

        $this->assertSame(
            $childUriList,
            $this->sut->findChildListUris(
                [
                    'parentListUri' => 'uri1'
                ]
            )
        );
    }
}
