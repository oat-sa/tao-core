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

        // Disable check statements by default to simplify testing
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
        // Clean up environment variables after each test
        unset($_ENV['TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS']);
        unset($_ENV['TAO_ID_GENERATOR_MAX_RETRIES']);
        unset($_ENV['TAO_ID_GENERATOR_ID_START']);
    }

    public function testGenerateWithInvalidResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required "resource" option that must be an instance of core_kernel_classes_Resource');

        $this->generator->generate(['resource' => new \stdClass()]);
    }

    public function testGenerateWithMissingResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required "resource" option that must be an instance of core_kernel_classes_Resource');

        $this->generator->generate([]);
    }

    public function testGetStartIdDefaultValue(): void
    {
        unset($_ENV['TAO_ID_GENERATOR_ID_START']);

        // Use reflection to test the private method
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
        $expectedId = '100000000';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->with([UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType], [UniqueIdRepository::FIELD_UNIQUE_ID => 'DESC'])
            ->willReturn(null);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => 'http://example.com/resource/123'
            ]);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateWithExistingIds(): void
    {
        $resourceType = 'test-resource-type';
        $lastId = 100000005;
        $expectedId = '100000006';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->with([UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType], [UniqueIdRepository::FIELD_UNIQUE_ID => 'DESC'])
            ->willReturn([UniqueIdRepository::FIELD_UNIQUE_ID => $lastId]);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => 'http://example.com/resource/123'
            ]);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateWithUniqueConstraintViolationRetry(): void
    {
        $resourceType = 'test-resource-type';
        $lastId = 100000005;
        $expectedId = '100000007';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->with([UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType], [UniqueIdRepository::FIELD_UNIQUE_ID => 'DESC'])
            ->willReturn([UniqueIdRepository::FIELD_UNIQUE_ID => $lastId]);

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
                // Allow any other ID to succeed (in case the generator tries more)
                return;
            });

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateFailsAfterMaxRetries(): void
    {
        $resourceType = 'test-resource-type';
        $lastId = 100000005;

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->with([UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType], [UniqueIdRepository::FIELD_UNIQUE_ID => 'DESC'])
            ->willReturn([UniqueIdRepository::FIELD_UNIQUE_ID => $lastId]);

        $driverException = $this->createMock(DriverException::class);
        $exception = new UniqueConstraintViolationException('Duplicate entry', $driverException);

        $this->uniqueIdRepository
            ->method('save')
            ->willThrowException($exception);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Max retries reached when trying to generate unique ID for resource type: test-resource-type');

        $this->generator->generate(['resource' => $this->resource]);
    }

    public function testGenerateThrowsExceptionWhenResourceOptionMissing(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing required "resource" option that must be an instance of core_kernel_classes_Resource');

        $this->generator->generate();
    }

    public function testGenerateThrowsExceptionWhenResourceLacksGetRootIdMethod(): void
    {
        $invalidResource = new \stdClass(); // Not a core_kernel_classes_Resource

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required "resource" option that must be an instance of core_kernel_classes_Resource');

        $this->generator->generate(['resource' => $invalidResource]);
    }

    public function testGenerateWithNullResourceId(): void
    {
        $resourceType = 'test-resource-type';
        $expectedId = '100000000';

        $resourceWithNullUri = $this->createMock(core_kernel_classes_Resource::class);
        $resourceWithNullUri->method('getRootId')->willReturn($resourceType);
        $resourceWithNullUri->method('getUri')->willReturn(''); // Empty string instead of null

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->with([UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType], [UniqueIdRepository::FIELD_UNIQUE_ID => 'DESC'])
            ->willReturn(null);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => ''
            ]);

        $result = $this->generator->generate(['resource' => $resourceWithNullUri]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateEnsuresNineDigitPadding(): void
    {
        $resourceType = 'test-resource-type';
        $startId = 1;
        $expectedId = '000000001';

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->with([UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType], [UniqueIdRepository::FIELD_UNIQUE_ID => 'DESC'])
            ->willReturn(null);

        // Set environment variable to test with a small start ID
        $_ENV['TAO_ID_GENERATOR_ID_START'] = (string)$startId;

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => 'http://example.com/resource/123'
            ]);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
        $this->assertEquals(9, strlen($result));
        $this->assertIsNumeric($result);
    }

    public function testGenerateIncrementalIds(): void
    {
        $baseId = 100000010;
        $expectedIds = ['100000011', '100000012', '100000013'];

        $this->uniqueIdRepository
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls(
                [UniqueIdRepository::FIELD_UNIQUE_ID => $baseId],
                [UniqueIdRepository::FIELD_UNIQUE_ID => $baseId + 1],
                [UniqueIdRepository::FIELD_UNIQUE_ID => $baseId + 2]
            );

        $this->uniqueIdRepository
            ->method('save');

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
            ->method('findOneBy')
            ->with([UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType], [UniqueIdRepository::FIELD_UNIQUE_ID => 'DESC'])
            ->willReturn([UniqueIdRepository::FIELD_UNIQUE_ID => $lastId]);

        $this->complexSearch
            ->expects($this->never())
            ->method('query');

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => 'http://example.com/resource/123'
            ]);

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
            ->method('findOneBy')
            ->with([UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType], [UniqueIdRepository::FIELD_UNIQUE_ID => 'DESC'])
            ->willReturn([UniqueIdRepository::FIELD_UNIQUE_ID => $lastId]);

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
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => 'http://example.com/resource/123'
            ]);

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
            ->method('findOneBy')
            ->with([UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType], [UniqueIdRepository::FIELD_UNIQUE_ID => 'DESC'])
            ->willReturn([UniqueIdRepository::FIELD_UNIQUE_ID => $lastId]);

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
            ->method('save')
            ->with([
                UniqueIdRepository::FIELD_RESOURCE_TYPE => $resourceType,
                UniqueIdRepository::FIELD_UNIQUE_ID => $expectedId,
                UniqueIdRepository::FIELD_RESOURCE_ID => 'http://example.com/resource/123'
            ]);

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
