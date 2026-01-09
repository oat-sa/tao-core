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

namespace oat\tao\test\unit\models\classes\StatisticalMetadata\Import\Processor;

use Iterator;
use PHPUnit\Framework\TestCase;
use oat\oatbox\filesystem\File;
use oat\oatbox\reporting\Report;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\tao\model\Csv\Resource\Reader;
use oat\generis\test\IteratorMockTrait;
use oat\tao\model\Csv\Factory\ReaderFactory;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataAliasesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Processor\NotifyImportService;
use PHPUnit\Framework\MockObject\MockObject;
use tao_models_classes_dataBinding_GenerisInstanceDataBinder;
use oat\tao\model\StatisticalMetadata\Import\Builder\ReportBuilder;
use oat\tao\model\StatisticalMetadata\Import\Processor\ImportProcessor;
use oat\tao\model\StatisticalMetadata\Import\Validator\HeaderValidator;
use oat\tao\model\StatisticalMetadata\Import\Extractor\ResourceExtractor;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataValuesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataPropertiesExtractor;

class ImportProcessorTest extends TestCase
{
    use IteratorMockTrait;

    /** @var Reader|MockObject */
    private $csv;

    /** @var ReaderFactory|MockObject */
    private $readerFactory;

    /** @var HeaderValidator|MockObject */
    private $headerValidator;

    /** @var MetadataPropertiesExtractor|MockObject */
    private $metadataPropertiesExtractor;

    /** @var ResourceExtractor|MockObject */
    private $resourceExtractor;

    /** @var MetadataValuesExtractor|MockObject */
    private $metadataValuesExtractor;

    /** @var ReportBuilder|MockObject */
    private $reportBuilder;

    /** @var tao_models_classes_dataBinding_GenerisInstanceDataBinder|MockObject */
    private $dataBinder;

    /** @var NotifyImportService|MockObject */
    private $notifyImportService;

    /** @var MetadataAliasesExtractor|MockObject */
    private $metadataAliasesExtractor;

    /** @var ImportProcessor */
    private $sut;

    protected function setUp(): void
    {
        $this->csv = $this->createMock(Reader::class);

        $this->readerFactory =  $this->createMock(ReaderFactory::class);
        $this->headerValidator =  $this->createMock(HeaderValidator::class);
        $this->metadataPropertiesExtractor =  $this->createMock(MetadataPropertiesExtractor::class);
        $this->metadataAliasesExtractor =  $this->createMock(MetadataAliasesExtractor::class);
        $this->resourceExtractor =  $this->createMock(ResourceExtractor::class);
        $this->metadataValuesExtractor =  $this->createMock(MetadataValuesExtractor::class);
        $this->reportBuilder =  $this->createMock(ReportBuilder::class);
        $this->dataBinder = $this->createMock(tao_models_classes_dataBinding_GenerisInstanceDataBinder::class);
        $this->notifyImportService = $this->createMock(NotifyImportService::class);

        $this->sut = new ImportProcessor(
            $this->readerFactory,
            $this->headerValidator,
            $this->metadataPropertiesExtractor,
            $this->metadataAliasesExtractor,
            $this->resourceExtractor,
            $this->metadataValuesExtractor,
            $this->reportBuilder,
            $this->notifyImportService
        );
        $this->sut->withDataBinder($this->dataBinder);
    }

    public function testProcess(): void
    {
        $stream = fopen('php://memory', 'rb+');

        $file = $this->createMock(File::class);
        $file
            ->expects($this->once())
            ->method('readStream')
            ->willReturn($stream);

        $this->readerFactory
            ->expects($this->once())
            ->method('createFromStream')
            ->with($stream, [ReaderFactory::DELIMITER => ';'])
            ->willReturn($this->csv);

        $header = [
            'itemId',
            'testId',
            'metadata_alias',
        ];

        $this->csv
            ->expects($this->once())
            ->method('getHeader')
            ->willReturn($header);

        $this->headerValidator
            ->expects($this->once())
            ->method('validateRequiredHeaders')
            ->with($header);

        $metadataProperty = $this->createMock(core_kernel_classes_Property::class);
        $metadataProperties = [$metadataProperty];

        $this->metadataPropertiesExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($header)
            ->willReturn($metadataProperties);

        $this->metadataAliasesExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($header)
            ->willReturn(['someAlias']);

        $this->notifyImportService
            ->expects($this->once())
            ->method('withAliases')
            ->with(['someAlias']);

        $this->notifyImportService
            ->expects($this->once())
            ->method('notify');

        $records = [
            [
                'itemId' => 'id',
                'testId' => '',
                'metadata_alias' => 'data',
            ]
        ];

        $this->csv
            ->expects($this->once())
            ->method('getRecords')
            ->with($header)
            ->willReturn(
                $this->createIteratorMock(
                    Iterator::class,
                    $records,
                    ['count']
                )
            );

        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $this->resourceExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($records[0])
            ->willReturn($resource);

        $metadataValues = [
            'metadataPropertyUri' => 'data',
        ];

        $this->metadataValuesExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($records[0], $resource, $metadataProperties)
            ->willReturn($metadataValues);

        $this->dataBinder
            ->expects($this->once())
            ->method('bind')
            ->with($metadataValues);

        $this->dataBinder
            ->expects($this->once())
            ->method('forceModification');

        $report = $this->createMock(Report::class);

        $this->reportBuilder
            ->expects($this->once())
            ->method('buildByResult')
            ->willReturn($report);

        $this->assertEquals($report, $this->sut->process($file));
    }
}
