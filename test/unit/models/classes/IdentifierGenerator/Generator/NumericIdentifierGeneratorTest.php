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
 * Copyright (c) 2024-2025 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\IdentifierGenerator\Generator;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Exception;
use InvalidArgumentException;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\QueryInterface;
use oat\search\helper\SupportedOperatorHelper;
use oat\tao\model\IdentifierGenerator\Generator\NumericIdentifierGenerator;
use oat\tao\model\IdentifierGenerator\Repository\UniqueIdRepository;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Resource;

class NumericIdentifierGeneratorTest extends TestCase
{
    private NumericIdentifierGenerator $generator;
    private MockObject $uniqueIdRepository;
    private MockObject $complexSearch;
    private MockObject $resource;

    protected function setUp(): void
    {
        $this->uniqueIdRepository = $this->createMock(UniqueIdRepository::class);
        $this->complexSearch = $this->createMock(ComplexSearchService::class);

        $this->resource = $this->createMock(core_kernel_classes_Resource::class);
        $this->resource->method('getRootId')->willReturn('test-resource-type');
        $this->resource->method('getUri')->willReturn('http://example.com/resource/123');

        $this->generator = new NumericIdentifierGenerator(
            $this->uniqueIdRepository,
            $this->complexSearch,
            100,
            false,
            1
        );
    }

    public function testGenerateMissingResourceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required "resource" option that must be an instance of core_kernel_classes_Resource'
        );

        $this->generator->generate([]);
    }

    public function testGenerateInvalidResourceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required "resource" option that must be an instance of core_kernel_classes_Resource'
        );

        $this->generator->generate(['resource' => new \stdClass()]);
    }

    public function testGenerateReturnsExistingIdWhenResourceAlreadyHasOne(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $existingId = '100000042';

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId])
            ->willReturn([
                UniqueIdRepository::FIELD_UNIQUE_ID => $existingId,
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ]);

        $this->uniqueIdRepository->expects($this->never())->method('save');

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($existingId, $result);
    }

    public function testGenerateWithNoExistingIds(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $expectedId = '000000001';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, $orderBy = []) use ($resourceType, $resourceId) {
                if (isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID])) {
                    return null;
                }
                if (isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE])) {
                    return null;
                }
                return null;
            });

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ]);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
        $this->assertEquals(9, strlen($result));
        $this->assertIsNumeric($result);
    }

    public function testGenerateWithExistingIds(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $lastId = 100000005;
        $expectedId = '100000006';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, $orderBy = []) use ($resourceType, $resourceId, $lastId) {
                if (isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID])) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE])
                    && isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                ) {
                    return [UniqueIdRepository::FIELD_UNIQUE_ID => $lastId];
                }
                return null;
            });

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ]);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateWithUniqueConstraintViolationRetry(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $lastId = 100000005;
        $expectedId = '100000007';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, $orderBy = []) use ($resourceType, $resourceId, $lastId) {
                if (isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID])) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE])
                    && isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                ) {
                    return [UniqueIdRepository::FIELD_UNIQUE_ID => $lastId];
                }
                return null;
            });

        $driverException = $this->createMock(DriverException::class);
        $exception = new UniqueConstraintViolationException($driverException);

        $this->uniqueIdRepository
            ->method('save')
            ->willReturnCallback(function ($data) use ($exception, $expectedId) {
                if ($data[UniqueIdRepository::FIELD_UNIQUE_ID] === '100000006') {
                    throw $exception;
                }
                if ($data[UniqueIdRepository::FIELD_UNIQUE_ID] === $expectedId) {
                    return;
                }
            });

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateFailsAfterMaxRetries(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $lastId = 100000005;

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, $orderBy = []) use ($resourceType, $resourceId, $lastId) {
                if (isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID])) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE])
                    && isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                ) {
                    return [UniqueIdRepository::FIELD_UNIQUE_ID => $lastId];
                }
                return null;
            });

        $driverException = $this->createMock(DriverException::class);
        $exception = new UniqueConstraintViolationException($driverException);

        $this->uniqueIdRepository
            ->method('save')
            ->willThrowException($exception);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Failed to generate unique ID for resource type 'test-resource-type' after 100 retries"
        );

        $this->generator->generate(['resource' => $this->resource]);
    }

    public function testGenerateWithCheckStatementsDisabled(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $expectedId = '000000001';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->complexSearch->expects($this->never())->method('query');

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ]);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateWithCheckStatementsEnabled(): void
    {
        $generator = new NumericIdentifierGenerator(
            $this->uniqueIdRepository,
            $this->complexSearch,
            100,
            true,
            1
        );

        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $expectedId = '000000001';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturn(null);

        $mockQueryBuilder = $this->createMock(QueryBuilderInterface::class);
        $mockQuery = $this->createMock(QueryInterface::class);
        $mockGateway = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['search'])
            ->getMock();

        $this->complexSearch
            ->method('query')
            ->willReturn($mockQueryBuilder);

        $this->complexSearch
            ->method('searchType')
            ->with($mockQueryBuilder, $resourceType, true)
            ->willReturn($mockQuery);

        $mockQuery->expects($this->once())
            ->method('addCriterion')
            ->with(TaoOntology::PROPERTY_UNIQUE_IDENTIFIER, SupportedOperatorHelper::EQUAL, $expectedId);

        $mockQueryBuilder->expects($this->once())
            ->method('setCriteria')
            ->with($mockQuery);

        $this->complexSearch
            ->method('getGateway')
            ->willReturn($mockGateway);

        $mockGateway
            ->method('search')
            ->willReturn(new \ArrayIterator([]));

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ]);

        $result = $generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateWithCheckStatementsEnabledIdExistsInStatements(): void
    {
        $generator = new NumericIdentifierGenerator(
            $this->uniqueIdRepository,
            $this->complexSearch,
            100,
            true,
            1
        );

        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $conflictingId = '000000001';
        $expectedId = '000000002';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturn(null);

        $mockQueryBuilder = $this->createMock(QueryBuilderInterface::class);
        $mockQuery = $this->createMock(QueryInterface::class);
        $mockGateway = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['search'])
            ->getMock();

        $this->complexSearch
            ->method('query')
            ->willReturn($mockQueryBuilder);

        $this->complexSearch
            ->method('searchType')
            ->with($mockQueryBuilder, $resourceType, true)
            ->willReturn($mockQuery);

        $this->complexSearch
            ->method('getGateway')
            ->willReturn($mockGateway);

        $mockGateway
            ->method('search')
            ->willReturnCallback(function () use ($conflictingId) {
                static $callCount = 0;
                $callCount++;
                if ($callCount === 1) {
                    return new \ArrayIterator([new \stdClass()]);
                }
                return new \ArrayIterator([]);
            });

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ]);

        $result = $generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testConstructorDefaults(): void
    {
        $generator = new NumericIdentifierGenerator(
            $this->uniqueIdRepository,
            $this->complexSearch,
            null,
            null,
            null
        );

        $reflection = new \ReflectionClass($generator);

        $maxRetriesProperty = $reflection->getProperty('maxRetries');
        $maxRetriesProperty->setAccessible(true);
        $this->assertEquals(100, $maxRetriesProperty->getValue($generator));

        $shouldCheckStatementsProperty = $reflection->getProperty('shouldCheckStatements');
        $shouldCheckStatementsProperty->setAccessible(true);
        $this->assertTrue($shouldCheckStatementsProperty->getValue($generator));

        $startIdProperty = $reflection->getProperty('startId');
        $startIdProperty->setAccessible(true);
        $this->assertEquals(1, $startIdProperty->getValue($generator));
    }

    public function testConstructorWithCustomValues(): void
    {
        $generator = new NumericIdentifierGenerator(
            $this->uniqueIdRepository,
            $this->complexSearch,
            50,
            false,
            100000000
        );

        $reflection = new \ReflectionClass($generator);

        $maxRetriesProperty = $reflection->getProperty('maxRetries');
        $maxRetriesProperty->setAccessible(true);
        $this->assertEquals(50, $maxRetriesProperty->getValue($generator));

        $shouldCheckStatementsProperty = $reflection->getProperty('shouldCheckStatements');
        $shouldCheckStatementsProperty->setAccessible(true);
        $this->assertFalse($shouldCheckStatementsProperty->getValue($generator));

        $startIdProperty = $reflection->getProperty('startId');
        $startIdProperty->setAccessible(true);
        $this->assertEquals(100000000, $startIdProperty->getValue($generator));
    }
}
