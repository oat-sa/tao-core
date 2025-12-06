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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\StatisticalMetadata\Import\Extractor;

use PHPUnit\Framework\TestCase;
use core_kernel_classes_Resource;
use core_kernel_classes_Property;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\model\resource\Contract\ResourceRepositoryInterface;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataAliasesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Exception\HeaderValidationException;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataPropertiesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Validator\MetadataPropertiesValidator;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;

class MetadataPropertiesExtractorTest extends TestCase
{
    /** @var MetadataAliasesExtractor|MockObject */
    private $metadataAliasesExtractor;

    /** @var ResourceRepositoryInterface|MockObject */
    private $resourceRepositoryInterface;

    /** @var MetadataPropertiesValidator|MockObject */
    private $metadataPropertiesValidator;

    /** @var MetadataPropertiesExtractor */
    private $sut;

    protected function setUp(): void
    {
        $this->metadataAliasesExtractor = $this->createMock(MetadataAliasesExtractor::class);
        $this->resourceRepositoryInterface = $this->createMock(ResourceRepositoryInterface::class);
        $this->metadataPropertiesValidator = $this->createMock(MetadataPropertiesValidator::class);

        $this->sut = new MetadataPropertiesExtractor(
            $this->metadataAliasesExtractor,
            $this->resourceRepositoryInterface,
            $this->metadataPropertiesValidator
        );
    }

    public function testExtractSuccess(): void
    {
        $header = [
            'itemId',
            'testId',
            'metadata_alias',
        ];
        $aliases = ['alias'];

        $this->metadataAliasesExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($header)
            ->willReturn($aliases);

        $property = $this->createProperty('alias');

        $this->resourceRepositoryInterface
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([$this->createResource('uri', $property)]);

        $this->metadataPropertiesValidator
            ->expects($this->once())
            ->method('validateMetadataExistence')
            ->with($aliases, [$property]);

        $this->metadataPropertiesValidator
            ->expects($this->once())
            ->method('validateMetadataUniqueness')
            ->with([$property]);

        $this->metadataPropertiesValidator
            ->expects($this->once())
            ->method('validateMetadataTypes')
            ->with([$property]);

        $this->metadataPropertiesValidator
            ->expects($this->once())
            ->method('validateMetadataIsStatistical')
            ->with([$property]);

        $this->assertEquals(
            ['metadata_alias' => $property],
            $this->sut->extract($header)
        );
    }

    /**
     * @dataProvider dataProviderExtractMetadataNotExists
     */
    public function testExtractMetadataNotExists(
        array $header,
        array $aliases,
        array $properties,
        array $resources,
        string $exceptionClass
    ): void {
        $this->metadataAliasesExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($header)
            ->willReturn($aliases);

        $this->resourceRepositoryInterface
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($resources);

        $this->metadataPropertiesValidator
            ->expects($this->once())
            ->method('validateMetadataExistence')
            ->with($aliases, $properties)
            ->willThrowException($this->createMock($exceptionClass));

        $this->metadataPropertiesValidator
            ->expects($this->never())
            ->method('validateMetadataUniqueness');

        $this->metadataPropertiesValidator
            ->expects($this->never())
            ->method('validateMetadataTypes');

        $this->metadataPropertiesValidator
            ->expects($this->never())
            ->method('validateMetadataIsStatistical');

        $this->expectException($exceptionClass);

        $this->sut->extract($header);
    }

    /**
     * @dataProvider dataProviderExtractMetadataNotUnique
     */
    public function testExtractMetadataNotUnique(
        array $header,
        array $aliases,
        array $properties,
        array $resources,
        string $exceptionClass
    ): void {
        $this->metadataAliasesExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($header)
            ->willReturn($aliases);

        $this->resourceRepositoryInterface
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($resources);

        $this->metadataPropertiesValidator
            ->expects($this->once())
            ->method('validateMetadataExistence')
            ->with($aliases, $properties);

        $this->metadataPropertiesValidator
            ->expects($this->once())
            ->method('validateMetadataUniqueness')
            ->with($properties)
            ->willThrowException($this->createMock($exceptionClass));

        $this->metadataPropertiesValidator
            ->expects($this->never())
            ->method('validateMetadataTypes');

        $this->metadataPropertiesValidator
            ->expects($this->never())
            ->method('validateMetadataIsStatistical');

        $this->expectException($exceptionClass);

        $this->sut->extract($header);
    }

    /**
     * @dataProvider dataProviderExtractMetadataInvalidType
     */
    public function testExtractMetadataInvalidType(
        array $header,
        array $aliases,
        array $properties,
        array $resources,
        string $exceptionClass
    ): void {
        $this->metadataAliasesExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($header)
            ->willReturn($aliases);

        $this->resourceRepositoryInterface
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($resources);

        $this->metadataPropertiesValidator
            ->expects($this->once())
            ->method('validateMetadataExistence')
            ->with($aliases, $properties);

        $this->metadataPropertiesValidator
            ->expects($this->once())
            ->method('validateMetadataUniqueness')
            ->with($properties);

        $this->metadataPropertiesValidator
            ->expects($this->once())
            ->method('validateMetadataTypes')
            ->with($properties)
            ->willThrowException($this->createMock($exceptionClass));

        $this->metadataPropertiesValidator
            ->expects($this->never())
            ->method('validateMetadataIsStatistical');

        $this->expectException($exceptionClass);

        $this->sut->extract($header);
    }

    public function dataProviderExtractMetadataNotExists(): array
    {
        $property = $this->createProperty('alias1');

        return [
            'partially' => [
                'header' => [
                    'itemId',
                    'testId',
                    'metadata_alias1',
                    'metadata_alias2',
                ],
                'aliases' => [
                    'alias1',
                    'alias2',
                ],
                'properties' => [
                    $property,
                ],
                'resources' => [
                    $this->createResource('uri1', $property),
                ],
                'exceptionClass' => HeaderValidationException::class,
            ],
            'fully' => [
                'header' => [
                    'itemId',
                    'testId',
                    'metadata_alias1',
                    'metadata_alias2',
                ],
                'aliases' => [
                    'alias1',
                    'alias2',
                ],
                'properties' => [],
                'resources' => [],
                'exceptionClass' => AggregatedValidationException::class,
            ],
        ];
    }

    public function dataProviderExtractMetadataNotUnique(): array
    {
        $property = $this->createProperty('alias');
        $notUniqueProperty1 = $this->createProperty('notUniqueAlias');
        $notUniqueProperty2 = $this->createProperty('notUniqueAlias');
        $notUniqueProperty3 = $this->createProperty('notUniqueAlias');
        $notUniqueProperty4 = $this->createProperty('notUniqueAlias');

        return [
            'partially' => [
                'header' => [
                    'itemId',
                    'testId',
                    'metadata_alias',
                    'metadata_notUniqueAlias',
                ],
                'aliases' => [
                    'alias',
                    'notUniqueAlias',
                ],
                'properties' => [
                    $property,
                    $notUniqueProperty1,
                    $notUniqueProperty2,
                ],
                'resources' => [
                    $this->createResource('uri', $property),
                    $this->createResource('uri1', $notUniqueProperty1),
                    $this->createResource('uri2', $notUniqueProperty2),
                ],
                'exceptionClass' => HeaderValidationException::class,
            ],
            'fully' => [
                'header' => [
                    'itemId',
                    'testId',
                    'metadata_alias1',
                    'metadata_alias2',
                ],
                'aliases' => [
                    'alias1',
                    'alias2',
                ],
                'properties' => [
                    $notUniqueProperty3,
                    $notUniqueProperty4,
                ],
                'resources' => [
                    $this->createResource('uri3', $notUniqueProperty3),
                    $this->createResource('uri4', $notUniqueProperty4),
                ],
                'exceptionClass' => AggregatedValidationException::class,
            ],
        ];
    }

    public function dataProviderExtractMetadataInvalidType(): array
    {
        $property = $this->createProperty('alias');
        $invalidTypeProperty1 = $this->createProperty('invalidType');
        $invalidTypeProperty2 = $this->createProperty('invalidType');

        return [
            'partially' => [
                'header' => [
                    'itemId',
                    'testId',
                    'metadata_alias',
                    'metadata_notUniqueAlias',
                ],
                'aliases' => [
                    'alias',
                    'notUniqueAlias',
                ],
                'properties' => [
                    $property,
                    $invalidTypeProperty1,
                ],
                'resources' => [
                    $this->createResource('uri', $property),
                    $this->createResource('uri1', $invalidTypeProperty1),
                ],
                'exceptionClass' => HeaderValidationException::class,
            ],
            'fully' => [
                'header' => [
                    'itemId',
                    'testId',
                    'metadata_alias1',
                    'metadata_alias2',
                ],
                'aliases' => [
                    'alias1',
                    'alias2',
                ],
                'properties' => [
                    $invalidTypeProperty2,
                ],
                'resources' => [
                    $this->createResource('uri2', $invalidTypeProperty2),
                ],
                'exceptionClass' => AggregatedValidationException::class,
            ],
        ];
    }

    private function createProperty(string $alias): core_kernel_classes_Property
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->expects($this->once())
            ->method('getAlias')
            ->willReturn($alias);

        return $property;
    }

    private function createResource(string $uri, core_kernel_classes_Property $property): core_kernel_classes_Resource
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $resource
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);
        $resource
            ->expects($this->once())
            ->method('getProperty')
            ->with($uri)
            ->willReturn($property);

        return $resource;
    }
}
