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
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataValuesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataHeadersExtractor;
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;
use oat\tao\model\StatisticalMetadata\Import\Validator\ResourceMetadataRelationValidator;

class MetadataValuesExtractorTest extends TestCase
{
    /** @var MetadataHeadersExtractor|MockObject */
    private $metadataHeadersExtractor;

    /** @var ResourceMetadataRelationValidator|MockObject */
    private $resourceMetadataRelationValidator;

    /** @var MetadataValuesExtractor */
    private $sut;

    protected function setUp(): void
    {
        $this->metadataHeadersExtractor = $this->createMock(MetadataHeadersExtractor::class);
        $this->resourceMetadataRelationValidator = $this->createMock(
            ResourceMetadataRelationValidator::class
        );

        $this->sut = new MetadataValuesExtractor(
            $this->metadataHeadersExtractor,
            $this->resourceMetadataRelationValidator
        );
    }

    public function testExtract(): void
    {
        $record = [
            'itemId' => 'itemUri',
            'testId' => '',
            'metadata_alias1' => 'alias1Value',
            'metadata_alias2' => '',
        ];

        $this->metadataHeadersExtractor
            ->expects($this->once())
            ->method('extract')
            ->with(array_keys($record))
            ->willReturn(
                [
                    'metadata_alias1',
                    'metadata_alias2',
                ]
            );

        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $metadataProperty = $this->createMock(core_kernel_classes_Property::class);
        $metadataProperty
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('metadataPropertyUri');

        $this->resourceMetadataRelationValidator
            ->expects($this->once())
            ->method('validate')
            ->with($resource, $metadataProperty);

        $this->assertEquals(
            ['metadataPropertyUri' => 'alias1Value'],
            $this->sut->extract(
                $record,
                $resource,
                ['metadata_alias1' => $metadataProperty]
            )
        );
    }

    public function testExtractWithInvalidRelation(): void
    {
        $record = [
            'itemId' => 'itemUri',
            'testId' => '',
            'metadata_alias' => 'alias1Value',
            'metadata_invalidRelation' => 'invalidRelationValue',
        ];

        $this->metadataHeadersExtractor
            ->expects($this->once())
            ->method('extract')
            ->with(array_keys($record))
            ->willReturn(
                [
                    'metadata_alias',
                    'metadata_invalidRelation',
                ]
            );

        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $metadataProperty = $this->createMock(core_kernel_classes_Property::class);
        $metadataProperty
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('metadataPropertyUri');

        $invalidMetadataProperty = $this->createMock(core_kernel_classes_Property::class);
        $invalidMetadataProperty
            ->expects($this->never())
            ->method('getUri');

        $this->resourceMetadataRelationValidator
            ->expects($this->exactly(2))
            ->method('validate')
            ->willReturnCallback(fn ($key, $value) => match ([$key, $value]) {
                [$resource, $invalidMetadataProperty] => throw $this->createMock(ErrorValidationException::class),
                default => null,
            });

        $this->expectException(AggregatedValidationException::class);

        $this->sut->extract(
            $record,
            $resource,
            [
                'metadata_alias' => $metadataProperty,
                'metadata_invalidRelation' => $invalidMetadataProperty,
            ]
        );
    }
}
