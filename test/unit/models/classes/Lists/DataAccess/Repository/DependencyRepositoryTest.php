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

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Driver\Statement;
use common_persistence_sql_Platform;
use Doctrine\DBAL\Query\QueryBuilder;
use common_persistence_SqlPersistence;
use oat\tao\model\Context\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\Lists\Business\Domain\Dependency;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use oat\tao\model\Lists\Business\Domain\DependencyCollection;
use oat\tao\model\Lists\DataAccess\Repository\DependencyRepository;
use oat\tao\model\Lists\Business\Domain\DependencyRepositoryContext;

class DependencyRepositoryTest extends TestCase
{
    /** @var DependencyRepository */
    private $sut;

    /** @var QueryBuilder|MockObject */
    private $queryBuilder;

    protected function setUp(): void
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

        $this->queryBuilder
            ->method('expr')
            ->willReturn($this->getExpressionBuilderMock());
        $this->queryBuilder
            ->method('execute')
            ->willReturn($this->createStatementMock(['uri']));
        $this->configureQueryBuilderSerfMethods(
            [
                'select',
                'from',
                'innerJoin',
                'andWhere',
                'setParameter',
            ]
        );

        $this->assertEquals(
            $collection,
            $this->sut->findAll(
                [
                    'listUri' => 'uri1',
                ]
            )
        );
    }

    public function testFindChildListIds(): void
    {
        $expectedChildIds = ['uri'];

        $this->queryBuilder
            ->method('expr')
            ->willReturn($this->getExpressionBuilderMock());
        $this->queryBuilder
            ->method('execute')
            ->willReturn($this->createStatementMock($expectedChildIds));
        $this->configureQueryBuilderSerfMethods(
            [
                'select',
                'from',
                'innerJoin',
                'andWhere',
                'setParameter',
            ]
        );

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

        $this->queryBuilder
            ->method('expr')
            ->willReturn($this->getExpressionBuilderMock());
        $this->queryBuilder
            ->method('execute')
            ->willReturn($this->createStatementMock($childUriList));
        $this->configureQueryBuilderSerfMethods(
            [
                'select',
                'from',
                'where',
                'innerJoin',
                'andWhere',
                'setParameter',
                'groupBy',
            ]
        );

        $this->assertSame(
            $childUriList,
            $this->sut->findChildListUris(
                [
                    'parentListUri' => 'uri1',
                ]
            )
        );
    }

    public function testFindChildListItemsUris(): void
    {
        $expected = ['uri'];

        $context = $this->createMock(ContextInterface::class);
        $context
            ->method('getParameter')
            ->willReturnCallback(
                static function (string $parameter) {
                    if ($parameter === DependencyRepositoryContext::PARAM_LIST_URIS) {
                        return ['listUri'];
                    }

                    if ($parameter === DependencyRepositoryContext::PARAM_DEPENDENCY_LIST_VALUES) {
                        return ['listValue'];
                    }

                    return null;
                }
            );

        $this->queryBuilder
            ->method('expr')
            ->willReturn($this->getExpressionBuilderMock());
        $this->queryBuilder
            ->method('execute')
            ->willReturn($this->createStatementMock($expected));
        $this->configureQueryBuilderSerfMethods(
            [
                'select',
                'from',
                'innerJoin',
                'where',
                'andWhere',
                'setParameter',
            ]
        );

        $this->assertSame($expected, $this->sut->findChildListItemsUris($context));
    }

    private function createStatementMock(array $expected): Statement
    {
        $statement = $this->createMock(Statement::class);
        $statement
            ->method('fetchAll')
            ->willReturn($expected);

        return $statement;
    }

    private function getExpressionBuilderMock(): ExpressionBuilder
    {
        return $this->createMock(ExpressionBuilder::class);
    }

    private function configureQueryBuilderSerfMethods(array $methods): void
    {
        foreach ($methods as $method) {
            $this->queryBuilder
                ->method($method)
                ->willReturnSelf();
        }
    }
}
