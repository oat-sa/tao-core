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
use oat\tao\model\TaoOntology;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\StatisticalMetadata\Import\Extractor\ResourceExtractor;
use oat\tao\model\StatisticalMetadata\Import\Validator\RecordResourceValidator;
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;

class ResourceExtractorTest extends TestCase
{
    /** @var core_kernel_classes_Resource|MockObject */
    private $resource;

    /** @var core_kernel_classes_Class|MockObject */
    private $itemRootClass;

    /** @var core_kernel_classes_Class|MockObject */
    private $testRootClass;

    /** @var RecordResourceValidator|MockObject */
    private $recordResourceValidator;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var ResourceExtractor */
    private $sut;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(core_kernel_classes_Resource::class);
        $this->itemRootClass = $this->createMock(core_kernel_classes_Class::class);
        $this->testRootClass = $this->createMock(core_kernel_classes_Class::class);

        $this->recordResourceValidator = $this->createMock(RecordResourceValidator::class);
        $this->ontology = $this->createMock(Ontology::class);

        $this->sut = new ResourceExtractor($this->recordResourceValidator, $this->ontology);
    }

    public function testExtract(): void
    {
        $record = [
            'itemId' => 'itemUri',
            'testId' => '',
            'metadata_alias' => 'aliasValue',
        ];

        $this->recordResourceValidator
            ->expects($this->once())
            ->method('validateResourceId')
            ->with($record);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('itemUri')
            ->willReturn($this->resource);

        $this->configureRootClassesMap();

        $this->recordResourceValidator
            ->expects($this->once())
            ->method('validateResourceAvailability')
            ->with($this->resource);

        $this->recordResourceValidator
            ->expects($this->once())
            ->method('validateResourceType')
            ->with($this->resource, $this->itemRootClass);

        $this->assertEquals($this->resource, $this->sut->extract($record));
    }

    public function testExtractWithoutId(): void
    {
        $record = [
            'itemId' => '',
            'testId' => '',
            'metadata_alias' => 'aliasValue',
        ];

        $this->recordResourceValidator
            ->expects($this->once())
            ->method('validateResourceId')
            ->with($record)
            ->willThrowException($this->createMock(ErrorValidationException::class));

        $this->ontology
            ->expects($this->never())
            ->method('getResource');
        $this->ontology
            ->expects($this->never())
            ->method('getClass');

        $this->recordResourceValidator
            ->expects($this->never())
            ->method('validateResourceAvailability');

        $this->recordResourceValidator
            ->expects($this->never())
            ->method('validateResourceType');

        $this->expectException(ErrorValidationException::class);

        $this->sut->extract($record);
    }

    public function testExtractWithNotExistingResource(): void
    {
        $record = [
            'itemId' => 'itemUri',
            'testId' => '',
            'metadata_alias' => 'aliasValue',
        ];

        $this->recordResourceValidator
            ->expects($this->once())
            ->method('validateResourceId')
            ->with($record);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('itemUri')
            ->willReturn($this->resource);
        $this->ontology
            ->expects($this->never())
            ->method('getClass');

        $exception = $this->createMock(ErrorValidationException::class);

        $this->recordResourceValidator
            ->expects($this->once())
            ->method('validateResourceAvailability')
            ->with($this->resource)
            ->willThrowException($exception);

        $exception
            ->expects($this->once())
            ->method('setColumn')
            ->with('itemId');

        $this->recordResourceValidator
            ->expects($this->never())
            ->method('validateResourceType');

        $this->expectException(ErrorValidationException::class);

        $this->sut->extract($record);
    }

    public function testExtractWithInvalidResourceType(): void
    {
        $record = [
            'itemId' => 'itemUri',
            'testId' => '',
            'metadata_alias' => 'aliasValue',
        ];

        $this->recordResourceValidator
            ->expects($this->once())
            ->method('validateResourceId')
            ->with($record);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('itemUri')
            ->willReturn($this->resource);

        $this->configureRootClassesMap();

        $this->recordResourceValidator
            ->expects($this->once())
            ->method('validateResourceAvailability')
            ->with($this->resource);

        $exception = $this->createMock(ErrorValidationException::class);

        $this->recordResourceValidator
            ->expects($this->once())
            ->method('validateResourceType')
            ->with($this->resource, $this->itemRootClass)
            ->willThrowException($exception);

        $exception
            ->expects($this->once())
            ->method('setColumn')
            ->with('itemId');

        $this->expectException(ErrorValidationException::class);

        $this->sut->extract($record);
    }

    private function configureRootClassesMap(): void
    {
        $this->ontology
            ->expects($this->exactly(2))
            ->method('getClass')
            ->willReturnCallback(
                function (string $uri): core_kernel_classes_Class {
                    if ($uri === TaoOntology::CLASS_URI_ITEM) {
                        return $this->itemRootClass;
                    }

                    if ($uri === TaoOntology::CLASS_URI_TEST) {
                        return $this->testRootClass;
                    }

                    return $this->createMock(core_kernel_classes_Class::class);
                }
            );
    }
}
