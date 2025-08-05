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
use Doctrine\DBAL\Driver\DriverException;
use Exception;
use InvalidArgumentException;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\QueryInterface;
use oat\tao\model\IdentifierGenerator\Generator\NumericIdentifierGenerator;
use oat\tao\model\IdentifierGenerator\Repository\UniqueIdRepository;
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

        $_ENV['TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS'] = 'false';

        $this->resource = $this->createMock(core_kernel_classes_Resource::class);
        $this->resource->method('getRootId')->willReturn('test-resource-type');
        $this->resource->method('getUri')->willReturn('http://example.com/resource/123');

        $this->generator = new NumericIdentifierGenerator(
            $this->uniqueIdRepository,
            $this->complexSearch
        );
    }

    protected function tearDown(): void
    {
        unset($_ENV['TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS']);
        unset($_ENV['TAO_ID_GENERATOR_MAX_RETRIES']);
        unset($_ENV['TAO_ID_GENERATOR_ID_START']);
    }

    public function testGenerateWithInvalidResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required "resource" option that must be an instance of core_kernel_classes_Resource'
        );

        $this->generator->generate(['resource' => new \stdClass()]);
    }

    public function testGenerateWithMissingResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required "resource" option that must be an instance of core_kernel_classes_Resource'
        );

        $this->generator->generate([]);
    }

    public function testGetStartIdDefaultValue(): void
    {
        unset($_ENV['TAO_ID_GENERATOR_ID_START']);

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getStartId');
        $method->setAccessible(true);

        $result = $method->invoke($this->generator);

        $this->assertEquals(100000000, $result);
    }

    public function testGetStartIdWithDifferentValues(): void
    {
        $testValues = [
            '0' => 0,
            '150000000' => 150000000,
            '500000000' => 500000000,
            '999999999' => 999999999,
        ];

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getStartId');
        $method->setAccessible(true);

        foreach ($testValues as $envValue => $expectedResult) {
            $_ENV['TAO_ID_GENERATOR_ID_START'] = $envValue;

            $result = $method->invoke($this->generator);

            $this->assertEquals($expectedResult, $result);

            unset($_ENV['TAO_ID_GENERATOR_ID_START']);
        }
    }

    public function testGenerateWithNoExistingIds(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $expectedId = '100000000';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, $orderBy = []) use ($resourceType, $resourceId) {
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_ID] === $resourceId
                ) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE] === $resourceType &&
                    isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                ) {
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

        $this->resource
            ->method('getRootId')
            ->willReturn($resourceType);

        $this->resource
            ->method('getUri')
            ->willReturn($resourceId);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
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
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_ID] === $resourceId
                ) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE] === $resourceType &&
                    isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
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

        $this->resource
            ->method('getRootId')
            ->willReturn($resourceType);

        $this->resource
            ->method('getUri')
            ->willReturn($resourceId);

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
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_ID] === $resourceId
                ) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE] === $resourceType &&
                    isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                ) {
                    return [UniqueIdRepository::FIELD_UNIQUE_ID => $lastId];
                }
                return null;
            });

        $driverException = $this->createMock(DriverException::class);
        $exception = new UniqueConstraintViolationException('Duplicate entry', $driverException);

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

        $this->resource
            ->method('getRootId')
            ->willReturn($resourceType);

        $this->resource
            ->method('getUri')
            ->willReturn($resourceId);

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
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_ID] === $resourceId
                ) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE] === $resourceType &&
                    isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                ) {
                    return [UniqueIdRepository::FIELD_UNIQUE_ID => $lastId];
                }
                return null;
            });

        $driverException = $this->createMock(DriverException::class);
        $exception = new UniqueConstraintViolationException('Duplicate entry', $driverException);

        $this->uniqueIdRepository
            ->method('save')
            ->willThrowException($exception);

        $this->resource
            ->method('getRootId')
            ->willReturn($resourceType);

        $this->resource
            ->method('getUri')
            ->willReturn($resourceId);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Max retries reached when trying to generate unique ID for resource type: test-resource-type'
        );

        $this->generator->generate(['resource' => $this->resource]);
    }

    public function testGenerateThrowsExceptionWhenResourceOptionMissing(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Missing required "resource" option that must be an instance of core_kernel_classes_Resource'
        );

        $this->generator->generate();
    }

    public function testGenerateThrowsExceptionWhenResourceLacksGetRootIdMethod(): void
    {
        $invalidResource = new \stdClass();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required "resource" option that must be an instance of core_kernel_classes_Resource'
        );

        $this->generator->generate(['resource' => $invalidResource]);
    }

    public function testGenerateWithNullResourceId(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = '';
        $expectedId = '100000000';

        $resourceWithNullUri = $this->createMock(core_kernel_classes_Resource::class);
        $resourceWithNullUri->method('getRootId')->willReturn($resourceType);
        $resourceWithNullUri->method('getUri')->willReturn($resourceId);

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, $orderBy = []) use ($resourceType, $resourceId) {
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_ID] === $resourceId
                ) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE] === $resourceType &&
                    isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                ) {
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

        $result = $this->generator->generate(['resource' => $resourceWithNullUri]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateEnsuresNineDigitPadding(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $startId = 1;
        $expectedId = '000000001';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, $orderBy = []) use ($resourceType, $resourceId) {
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_ID] === $resourceId
                ) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE] === $resourceType &&
                    isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                ) {
                    return null;
                }
                return null;
            });

        $_ENV['TAO_ID_GENERATOR_ID_START'] = (string)$startId;

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ]);

        $this->resource
            ->method('getRootId')
            ->willReturn($resourceType);

        $this->resource
            ->method('getUri')
            ->willReturn($resourceId);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
        $this->assertEquals(9, strlen($result));
        $this->assertIsNumeric($result);
    }

    public function testGenerateIncrementalIds(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $baseId = 100000010;
        $expectedIds = ['100000011', '100000012', '100000013'];

        $callCount = 0;
        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnCallback(
                function ($criteria, $orderBy = []) use ($resourceType, $resourceId, $baseId, &$callCount) {
                    if (
                        isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID]) &&
                        $criteria[UniqueIdRepository::FIELD_RESOURCE_ID] === $resourceId
                    ) {
                        return null;
                    }
                    if (
                        isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE]) &&
                        $criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE] === $resourceType &&
                        isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                    ) {
                        $lastId = $baseId + $callCount;
                        $callCount++;
                        return [UniqueIdRepository::FIELD_UNIQUE_ID => $lastId];
                    }
                    return null;
                }
            );

        $this->uniqueIdRepository
            ->method('save');

        $this->resource
            ->method('getRootId')
            ->willReturn($resourceType);

        $this->resource
            ->method('getUri')
            ->willReturn($resourceId);

        $results = [];
        for ($i = 0; $i < 3; $i++) {
            $results[] = $this->generator->generate(['resource' => $this->resource]);
        }

        $this->assertEquals($expectedIds, $results);
    }

    public function testGenerateWithCheckStatementsDisabled(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $lastId = 100000005;
        $expectedId = '100000006';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, $orderBy = []) use ($resourceType, $resourceId, $lastId) {
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_ID] === $resourceId
                ) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE] === $resourceType &&
                    isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                ) {
                    return [UniqueIdRepository::FIELD_UNIQUE_ID => $lastId];
                }
                return null;
            });

        $this->complexSearch
            ->expects($this->never())
            ->method('query');

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ]);

        $this->resource
            ->method('getRootId')
            ->willReturn($resourceType);

        $this->resource
            ->method('getUri')
            ->willReturn($resourceId);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateWithCheckStatementsEnabled(): void
    {
        $_ENV['TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS'] = 'true';

        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $lastId = 100000005;
        $expectedId = '100000006';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, $orderBy = []) use ($resourceType, $resourceId, $lastId) {
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_ID] === $resourceId
                ) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE] === $resourceType &&
                    isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                ) {
                    return [UniqueIdRepository::FIELD_UNIQUE_ID => $lastId];
                }
                return null;
            });

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
            ->willReturn(new \ArrayIterator([]));

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ]);

        $this->resource
            ->method('getRootId')
            ->willReturn($resourceType);

        $this->resource
            ->method('getUri')
            ->willReturn($resourceId);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);

        unset($_ENV['TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS']);
    }

    public function testGenerateWithCheckStatementsEnabledIdExistsInStatements(): void
    {
        $_ENV['TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS'] = 'true';

        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $lastId = 100000005;
        $expectedId = '100000007';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, $orderBy = []) use ($resourceType, $resourceId, $lastId) {
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_ID]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_ID] === $resourceId
                ) {
                    return null;
                }
                if (
                    isset($criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE]) &&
                    $criteria[UniqueIdRepository::FIELD_RESOURCE_TYPE] === $resourceType &&
                    isset($orderBy[UniqueIdRepository::FIELD_UNIQUE_ID])
                ) {
                    return [UniqueIdRepository::FIELD_UNIQUE_ID => $lastId];
                }
                return null;
            });

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
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([new \stdClass()]),
                new \ArrayIterator([])
            );

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ]);

        $this->resource
            ->method('getRootId')
            ->willReturn($resourceType);

        $this->resource
            ->method('getUri')
            ->willReturn($resourceId);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);

        unset($_ENV['TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS']);
    }

    public function testCheckIdExistsInStatementsPrivateMethod(): void
    {
        $resourceType = 'test-resource-type';
        $uniqueId = '100000006';

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
            ->willReturn(new \ArrayIterator([new \stdClass()]));

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('checkIdExistsInStatements');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->generator, [$resourceType, $uniqueId]);

        $this->assertTrue($result);
    }

    public function testGenerateReturnsExistingIdWhenResourceAlreadyHasOne(): void
    {
        $resourceType = 'test-resource-type';
        $resourceId = 'http://example.com/resource/123';
        $existingId = '100000042';

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ])
            ->willReturn([
                UniqueIdRepository::FIELD_UNIQUE_ID => $existingId,
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_RESOURCE_ID => $resourceId
            ]);

        $this->resource
            ->expects($this->once())
            ->method('getRootId')
            ->willReturn($resourceType);

        $this->resource
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($resourceId);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($existingId, $result);
    }
}
