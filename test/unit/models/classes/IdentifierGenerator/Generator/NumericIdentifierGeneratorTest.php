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
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\QueryInterface;
use oat\tao\model\IdentifierGenerator\Generator\NumericIdentifierGenerator;
use oat\tao\model\IdentifierGenerator\Repository\UniqueIdRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

        $this->resource = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getRootId', 'getUri'])
            ->getMock();

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
    }

    public function testGenerateWithNoExistingIds(): void
    {
        $resourceType = 'test-resource-type';
        $startId = 100000000;
        $expectedId = '100000000';

        $this->uniqueIdRepository
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn(null);

        $this->uniqueIdRepository
            ->method('getStartId')
            ->willReturn($startId);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('insertUniqueId')
            ->with($resourceType, $expectedId, 'http://example.com/resource/123');

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateWithExistingIds(): void
    {
        $resourceType = 'test-resource-type';
        $lastId = 100000005;
        $expectedId = '100000006';

        $this->uniqueIdRepository
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn($lastId);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('insertUniqueId')
            ->with($resourceType, $expectedId, 'http://example.com/resource/123');

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateWithUniqueConstraintViolationRetry(): void
    {
        $resourceType = 'test-resource-type';
        $lastId = 100000005;
        $expectedId = '100000007';

        $this->uniqueIdRepository
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn($lastId);

        $driverException = $this->createMock(DriverException::class);
        $exception = new UniqueConstraintViolationException('Duplicate entry', $driverException);

        $this->uniqueIdRepository
            ->method('insertUniqueId')
            ->willReturnCallback(function ($resourceType, $uniqueId, $resourceId) use ($exception, $expectedId) {
                if ($uniqueId === '100000006') {
                    throw $exception;
                }
                if ($uniqueId === $expectedId) {
                    return;
                }
                throw new Exception('Unexpected ID: ' . $uniqueId);
            });

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateFailsAfterMaxRetries(): void
    {
        $resourceType = 'test-resource-type';
        $lastId = 100000005;

        $this->uniqueIdRepository
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn($lastId);

        $driverException = $this->createMock(DriverException::class);
        $exception = new UniqueConstraintViolationException('Duplicate entry', $driverException);

        $this->uniqueIdRepository
            ->method('insertUniqueId')
            ->willThrowException($exception);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Failed to generate unique ID for resource type 'test-resource-type' after 200 retries"
        );

        $this->generator->generate(['resource' => $this->resource]);
    }

    public function testGenerateThrowsExceptionWhenResourceOptionMissing(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing required "resource" option with getRootId() method');

        $this->generator->generate();
    }

    public function testGenerateThrowsExceptionWhenResourceLacksGetRootIdMethod(): void
    {
        $invalidResource = new \stdClass();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing required "resource" option with getRootId() method');

        $this->generator->generate(['resource' => $invalidResource]);
    }

    public function testGenerateWithNullResourceId(): void
    {
        $resourceType = 'test-resource-type';
        $startId = 100000000;
        $expectedId = '100000000';

        $resourceWithNullUri = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getRootId', 'getUri'])
            ->getMock();
        $resourceWithNullUri->method('getRootId')->willReturn($resourceType);
        $resourceWithNullUri->method('getUri')->willReturn(null);

        $this->uniqueIdRepository
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn(null);

        $this->uniqueIdRepository
            ->method('getStartId')
            ->willReturn($startId);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('insertUniqueId')
            ->with($resourceType, $expectedId, null);

        $result = $this->generator->generate(['resource' => $resourceWithNullUri]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateEnsuresNineDigitPadding(): void
    {
        $resourceType = 'test-resource-type';
        $startId = 1;
        $expectedId = '000000001';
        $resourceId = 'http://example.com/resource/123';

        $this->uniqueIdRepository
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn(null);

        $this->uniqueIdRepository
            ->method('getStartId')
            ->willReturn($startId);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('insertUniqueId')
            ->with($resourceType, $expectedId, $resourceId);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
        $this->assertEquals(9, strlen($result));
    }

    public function testGenerateIncrementalIds(): void
    {
        $baseId = 100000010;
        $expectedIds = ['100000011', '100000012', '100000013'];

        $this->uniqueIdRepository
            ->method('getLastIdForResourceType')
            ->willReturnOnConsecutiveCalls($baseId, $baseId + 1, $baseId + 2);

        $this->uniqueIdRepository
            ->method('insertUniqueId');

        $results = [];
        for ($i = 0; $i < 3; $i++) {
            $results[] = $this->generator->generate(['resource' => $this->resource]);
        }

        $this->assertEquals($expectedIds, $results);
    }

    public function testGenerateWithCheckStatementsDisabled(): void
    {
        $resourceType = 'test-resource-type';
        $lastId = 100000005;
        $expectedId = '100000006';

        $this->uniqueIdRepository
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn($lastId);

        $this->complexSearch
            ->expects($this->never())
            ->method('query');

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('insertUniqueId')
            ->with($resourceType, $expectedId, 'http://example.com/resource/123');

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateWithCheckStatementsEnabled(): void
    {
        $_ENV['TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS'] = 'true';

        $resourceType = 'test-resource-type';
        $lastId = 100000005;
        $expectedId = '100000006';

        $this->uniqueIdRepository
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn($lastId);

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
            ->with($mockQueryBuilder)
            ->willReturn(new \ArrayIterator([]));

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('insertUniqueId')
            ->with($resourceType, $expectedId, 'http://example.com/resource/123');

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);

        unset($_ENV['TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS']);
    }

    public function testGenerateWithCheckStatementsEnabledIdExistsInStatements(): void
    {
        $_ENV['TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS'] = 'true';

        $resourceType = 'test-resource-type';
        $lastId = 100000005;
        $expectedId = '100000007';

        $this->uniqueIdRepository
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn($lastId);

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
            ->method('insertUniqueId')
            ->with($resourceType, $expectedId, 'http://example.com/resource/123');

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
            ->with($mockQueryBuilder)
            ->willReturn(new \ArrayIterator([new \stdClass()])); // Has results = ID exists

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('checkIdExistsInStatements');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->generator, [$resourceType, $uniqueId]);

        $this->assertTrue($result);
    }
}
