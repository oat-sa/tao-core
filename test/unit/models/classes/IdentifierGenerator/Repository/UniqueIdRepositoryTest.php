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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\IdentifierGenerator\Repository;

use common_persistence_SqlPersistence;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Driver\Exception as DriverException;
use common_persistence_sql_Platform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Result;
use InvalidArgumentException;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\log\LoggerService;
use oat\tao\model\IdentifierGenerator\Repository\UniqueIdRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UniqueIdRepositoryTest extends TestCase
{
    private UniqueIdRepository $repository;
    private MockObject $persistenceManager;
    private MockObject $logger;
    private MockObject $persistence;
    private MockObject $platform;
    private MockObject $queryBuilder;
    private MockObject $result;
    private MockObject $expressionBuilder;

    protected function setUp(): void
    {
        $this->persistenceManager = $this->createMock(PersistenceManager::class);
        $this->logger = $this->createMock(LoggerService::class);
        $this->persistence = $this->createMock(common_persistence_SqlPersistence::class);
        $this->platform = $this->createMock(common_persistence_sql_Platform::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->result = $this->createMock(Result::class);
        $this->expressionBuilder = $this->createMock(ExpressionBuilder::class);
    }

    private function setupMocks(): void
    {
        $this->persistenceManager->method('getPersistenceById')
            ->willReturn($this->persistence);

        $this->persistence->method('getPlatForm')
            ->willReturn($this->platform);

        $this->platform->method('getQueryBuilder')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->method('expr')
            ->willReturn($this->expressionBuilder);

        $this->expressionBuilder->method('eq')
            ->willReturn('field = :param');
    }

    public function testFindOneByWithCriteria(): void
    {
        $this->setupMocks();
        $this->repository = new UniqueIdRepository($this->persistenceManager, $this->logger);

        $criteria = ['resource_type' => 'test_type', 'unique_id' => 'test_id'];
        $expectedResult = [
            'resource_id' => 'resource123',
            'resource_type' => 'test_type',
            'unique_id' => 'test_id',
            'created_at' => '2025-01-01 12:00:00'
        ];

        $this->queryBuilder->expects($this->once())
            ->method('select')
            ->with('*')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with(UniqueIdRepository::TABLE_NAME)
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->exactly(2))
            ->method('andWhere')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->with(1)
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('execute')
            ->willReturn($this->result);

        $this->result->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn($expectedResult);

        $result = $this->repository->findOneBy($criteria);

        $this->assertEquals($expectedResult, $result);
    }

    public function testFindOneByWithCriteriaAndOrderBy(): void
    {
        $this->setupMocks();
        $this->repository = new UniqueIdRepository($this->persistenceManager, $this->logger);

        $criteria = ['resource_type' => 'test_type'];
        $orderBy = ['created_at' => 'DESC'];

        $this->queryBuilder->expects($this->once())
            ->method('select')
            ->with('*')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with(UniqueIdRepository::TABLE_NAME)
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('andWhere')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('setParameter')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('addOrderBy')
            ->with('created_at', 'DESC')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->with(1)
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('execute')
            ->willReturn($this->result);

        $this->result->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(false);

        $result = $this->repository->findOneBy($criteria, $orderBy);

        $this->assertNull($result);
    }

    public function testFindOneByReturnsNullWhenNoResults(): void
    {
        $this->setupMocks();
        $this->repository = new UniqueIdRepository($this->persistenceManager, $this->logger);

        $criteria = ['resource_type' => 'nonexistent'];

        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('andWhere')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setMaxResults')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('execute')->willReturn($this->result);

        $this->result->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(false);

        $result = $this->repository->findOneBy($criteria);

        $this->assertNull($result);
    }

    public function testSaveWithValidData(): void
    {
        $this->setupMocks();
        $this->repository = new UniqueIdRepository($this->persistenceManager, $this->logger);

        $data = [
            UniqueIdRepository::FIELD_RESOURCE_TYPE => 'test_type',
            UniqueIdRepository::FIELD_UNIQUE_ID => 'test_id',
            UniqueIdRepository::FIELD_RESOURCE_ID => 'resource123'
        ];

        $this->platform->expects($this->once())
            ->method('beginTransaction');

        $this->platform->expects($this->once())
            ->method('commit');

        $this->queryBuilder->expects($this->once())
            ->method('insert')
            ->with(UniqueIdRepository::TABLE_NAME)
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('values')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('setParameters')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('execute');

        $this->repository->save($data);
    }

    public function testSaveThrowsInvalidArgumentExceptionWhenMissingResourceType(): void
    {
        $this->repository = new UniqueIdRepository($this->persistenceManager, $this->logger);

        $data = [
            UniqueIdRepository::FIELD_UNIQUE_ID => 'test_id',
            UniqueIdRepository::FIELD_RESOURCE_ID => 'resource123'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('resourceType, uniqueId, and resourceId are all required');

        $this->repository->save($data);
    }

    public function testSaveThrowsInvalidArgumentExceptionWhenMissingUniqueId(): void
    {
        $this->repository = new UniqueIdRepository($this->persistenceManager, $this->logger);

        $data = [
            UniqueIdRepository::FIELD_RESOURCE_TYPE => 'test_type',
            UniqueIdRepository::FIELD_RESOURCE_ID => 'resource123'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('resourceType, uniqueId, and resourceId are all required');

        $this->repository->save($data);
    }

    public function testSaveThrowsInvalidArgumentExceptionWhenMissingResourceId(): void
    {
        $this->repository = new UniqueIdRepository($this->persistenceManager, $this->logger);

        $data = [
            UniqueIdRepository::FIELD_RESOURCE_TYPE => 'test_type',
            UniqueIdRepository::FIELD_UNIQUE_ID => 'test_id'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('resourceType, uniqueId, and resourceId are all required');

        $this->repository->save($data);
    }

    public function testSaveHandlesUniqueConstraintViolationException(): void
    {
        $this->setupMocks();
        $this->repository = new UniqueIdRepository($this->persistenceManager, $this->logger);

        $data = [
            UniqueIdRepository::FIELD_RESOURCE_TYPE => 'test_type',
            UniqueIdRepository::FIELD_UNIQUE_ID => 'test_id',
            UniqueIdRepository::FIELD_RESOURCE_ID => 'resource123'
        ];

        $driverException = $this->createMock(DriverException::class);
        $exception = new UniqueConstraintViolationException($driverException, null);

        $this->platform->expects($this->once())
            ->method('beginTransaction');

        $this->platform->expects($this->once())
            ->method('rollback');

        $this->queryBuilder->method('insert')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('values')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameters')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('execute')->willThrowException($exception);

        $this->expectException(UniqueConstraintViolationException::class);

        $this->repository->save($data);
    }

    public function testSaveHandlesGeneralDatabaseException(): void
    {
        $this->setupMocks();
        $this->repository = new UniqueIdRepository($this->persistenceManager, $this->logger);

        $data = [
            UniqueIdRepository::FIELD_RESOURCE_TYPE => 'test_type',
            UniqueIdRepository::FIELD_UNIQUE_ID => 'test_id',
            UniqueIdRepository::FIELD_RESOURCE_ID => 'resource123'
        ];

        $exception = new Exception('Database error');

        $this->platform->expects($this->once())
            ->method('beginTransaction');

        $this->platform->expects($this->once())
            ->method('rollback');

        $this->queryBuilder->method('insert')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('values')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameters')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('execute')->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Failed to save unique ID record', [
                'data' => $data,
                'error' => 'Database error'
            ]);

        $this->expectException(Exception::class);

        $this->repository->save($data);
    }

    public function testSaveUsesCorrectDateFormat(): void
    {
        $this->setupMocks();
        $this->repository = new UniqueIdRepository($this->persistenceManager, $this->logger);

        $data = [
            UniqueIdRepository::FIELD_RESOURCE_TYPE => 'test_type',
            UniqueIdRepository::FIELD_UNIQUE_ID => 'test_id',
            UniqueIdRepository::FIELD_RESOURCE_ID => 'resource123'
        ];

        $this->platform->method('beginTransaction');
        $this->platform->method('commit');
        $this->queryBuilder->method('insert')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('values')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('execute');

        $this->queryBuilder->expects($this->once())
            ->method('setParameters')
            ->with(
                $this->callback(function ($params) {
                    return isset($params['createdAt']) &&
                        preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $params['createdAt']);
                })
            )
            ->willReturn($this->queryBuilder);

        $this->repository->save($data);
    }
}
