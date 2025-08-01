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
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\IdentifierGenerator\Generator\NumericIdentifierGenerator;
use oat\tao\model\IdentifierGenerator\Repository\UniqueIdRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NumericIdentifierGeneratorTest extends TestCase
{
    private NumericIdentifierGenerator $generator;
    private MockObject $uniqueIdRepository;
    private MockObject $complexSearch;
    private MockObject $featureFlagChecker;
    private MockObject $resource;

    protected function setUp(): void
    {
        $this->uniqueIdRepository = $this->createMock(UniqueIdRepository::class);
        $this->complexSearch = $this->createMock(ComplexSearchService::class);
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);

        $this->resource = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getRootId', 'getUri'])
            ->getMock();

        $this->resource->method('getRootId')->willReturn('test-resource-type');
        $this->resource->method('getUri')->willReturn('http://example.com/resource/123');

        $this->generator = new NumericIdentifierGenerator(
            $this->uniqueIdRepository,
            $this->complexSearch,
            $this->featureFlagChecker
        );
    }

    public function testGenerateWithNoExistingIds(): void
    {
        $resourceType = 'test-resource-type';
        $startId = 100000000;
        $expectedId = '100000000';
        $resourceId = 'http://example.com/resource/123';

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn(null);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('getStartId')
            ->willReturn($startId);

        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->with('FEATURE_FLAG_CHECK_STATEMENTS')
            ->willReturn(false);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('insertUniqueId')
            ->with($resourceType, $expectedId, $resourceId);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
        $this->assertEquals(9, strlen($result));
        $this->assertIsNumeric($result);
    }

    public function testGenerateWithExistingIds(): void
    {
        $resourceType = 'test-resource-type';
        $lastId = 100000005;
        $expectedId = '100000006';
        $resourceId = 'http://example.com/resource/123';

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn($lastId);

        $this->uniqueIdRepository
            ->expects($this->never())
            ->method('getStartId');

        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->with('FEATURE_FLAG_CHECK_STATEMENTS')
            ->willReturn(false);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('insertUniqueId')
            ->with($resourceType, $expectedId, $resourceId);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateWithFeatureFlagDisabled(): void
    {
        $resourceType = 'test-resource-type';
        $lastId = 100000005;
        $expectedId = '100000006';
        $resourceId = 'http://example.com/resource/123';

        $this->uniqueIdRepository
            ->method('getLastIdForResourceType')
            ->willReturn($lastId);

        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->with('FEATURE_FLAG_CHECK_STATEMENTS')
            ->willReturn(false);

        $this->complexSearch
            ->expects($this->never())
            ->method('query');

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('insertUniqueId')
            ->with($resourceType, $expectedId, $resourceId);

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateWithUniqueConstraintViolationRetry(): void
    {
        $resourceType = 'test-resource-type';
        $lastId = 100000005;
        $conflictId = '100000006';
        $expectedId = '100000007';
        $resourceId = 'http://example.com/resource/123';

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn($lastId);

        $this->featureFlagChecker
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->with('FEATURE_FLAG_CHECK_STATEMENTS')
            ->willReturn(false);

        $driverException = $this->createMock(DriverException::class);
        $exception = new UniqueConstraintViolationException('Duplicate entry', $driverException);

        $this->uniqueIdRepository
            ->expects($this->exactly(2))
            ->method('insertUniqueId')
            ->withConsecutive(
                [$resourceType, $conflictId, $resourceId],
                [$resourceType, $expectedId, $resourceId]
            )
            ->willReturnOnConsecutiveCalls(
                $this->throwException($exception),
                null
            );

        $result = $this->generator->generate(['resource' => $this->resource]);

        $this->assertEquals($expectedId, $result);
    }

    public function testGenerateFailsAfterMaxRetries(): void
    {
        $resourceType = 'test-resource-type';
        $lastId = 100000005;

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn($lastId);

        $this->featureFlagChecker
            ->expects($this->exactly(200))
            ->method('isEnabled')
            ->with('FEATURE_FLAG_CHECK_STATEMENTS')
            ->willReturn(false);

        $driverException = $this->createMock(DriverException::class);
        $exception = new UniqueConstraintViolationException('Duplicate entry', $driverException);

        $this->uniqueIdRepository
            ->expects($this->exactly(200))
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
            ->expects($this->once())
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn(null);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('getStartId')
            ->willReturn($startId);

        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->with('FEATURE_FLAG_CHECK_STATEMENTS')
            ->willReturn(false);

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
            ->expects($this->once())
            ->method('getLastIdForResourceType')
            ->with($resourceType)
            ->willReturn(null);

        $this->uniqueIdRepository
            ->expects($this->once())
            ->method('getStartId')
            ->willReturn($startId);

        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->with('FEATURE_FLAG_CHECK_STATEMENTS')
            ->willReturn(false);

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

        $this->featureFlagChecker
            ->method('isEnabled')
            ->willReturn(false);

        $this->uniqueIdRepository
            ->method('insertUniqueId');

        $results = [];
        for ($i = 0; $i < 3; $i++) {
            $results[] = $this->generator->generate(['resource' => $this->resource]);
        }

        $this->assertEquals($expectedIds, $results);
    }
}
