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
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\resources\Service;

use ArrayIterator;
use core_kernel_classes_Class;
use core_kernel_classes_ContainerCollection;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\generis\model\GenerisRdf;
use oat\generis\test\IteratorMockTrait;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\resources\Contract\ClassMetadataMapperInterface;
use oat\tao\model\resources\Service\InstanceMetadataCopier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InstanceMetadataCopierTest extends TestCase
{
    use IteratorMockTrait;

    private InstanceMetadataCopier $sut;

    /** @var ClassMetadataMapperInterface|MockObject */
    private ClassMetadataMapperInterface $classMetadataMapper;

    /** @var FileReferenceSerializer|MockObject */
    private FileReferenceSerializer $fileReferenceSerializer;

    /** @var FileSystemService|MockObject */
    private FileSystemService $fileSystemService;

    protected function setUp(): void
    {
        $this->classMetadataMapper = $this->createMock(ClassMetadataMapperInterface::class);
        $this->fileReferenceSerializer = $this->createMock(FileReferenceSerializer::class);
        $this->fileSystemService = $this->createMock(FileSystemService::class);

        $this->sut = new InstanceMetadataCopier(
            $this->classMetadataMapper,
            $this->fileReferenceSerializer,
            $this->fileSystemService
        );
    }

    public function testCopy(): void
    {
        $fromInstance = $this->createMock(core_kernel_classes_Resource::class);
        $toInstance = $this->createMock(core_kernel_classes_Resource::class);

        $toClass = $this->createMock(core_kernel_classes_Class::class);

        $toInstance
            ->expects($this->once())
            ->method('getTypes')
            ->willReturn([$toClass]);

        $commonDestinationProperty = $this->createMock(core_kernel_classes_Property::class);
        $fileDestinationProperty = $this->createMock(core_kernel_classes_Property::class);

        $toClass
            ->expects($this->once())
            ->method('getProperties')
            ->with(true)
            ->willReturn([$commonDestinationProperty, $fileDestinationProperty]);

        $this->classMetadataMapper
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                [$commonDestinationProperty, 'commonOriginalPropertyUri'],
                [$fileDestinationProperty, null],
            ]);

        $commonOriginalProperty = $this->createMock(core_kernel_classes_Property::class);

        $commonDestinationProperty
            ->expects($this->once())
            ->method('getProperty')
            ->with('commonOriginalPropertyUri')
            ->willReturn($commonOriginalProperty);

        $fileDestinationProperty
            ->expects($this->once())
            ->method('isCustom')
            ->willReturn(true);

        $commonOriginalProperty
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('commonOriginalPropertyUri');
        $fileDestinationProperty
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('fileDestinationPropertyUri');

        $commonOriginalPropertyValues = $this->createMock(core_kernel_classes_ContainerCollection::class);
        $commonOriginalPropertyValue = $this->createMock(core_kernel_classes_Resource::class);
        $fileDestinationPropertyValues = $this->createMock(
            core_kernel_classes_ContainerCollection::class
        );
        $fileDestinationPropertyValue = $this->createMock(core_kernel_classes_Resource::class);

        $fromInstance
            ->expects($this->exactly(2))
            ->method('getPropertyValuesCollection')
            ->willReturnCallback(
                fn (core_kernel_classes_Property $property) => $property === $commonOriginalProperty
                    ? $commonOriginalPropertyValues
                    : $fileDestinationPropertyValues
            );

        $commonOriginalPropertyValues
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(
                $this->createIteratorMock(
                    ArrayIterator::class,
                    [$commonOriginalPropertyValue]
                )
            );

        $fileDestinationPropertyValues
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(
                $this->createIteratorMock(
                    ArrayIterator::class,
                    [$fileDestinationPropertyValue]
                )
            );

        $commonOriginalPropertyRange = $this->createMock(core_kernel_classes_Class::class);

        $commonOriginalProperty
            ->expects($this->once())
            ->method('getRange')
            ->willReturn($commonOriginalPropertyRange);

        $commonOriginalPropertyRange
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('commonOriginalPropertyRangeUri');

        $fileDestinationPropertyRange = $this->createMock(core_kernel_classes_Class::class);

        $fileDestinationProperty
            ->expects($this->once())
            ->method('getRange')
            ->willReturn($fileDestinationPropertyRange);

        $fileDestinationPropertyRange
            ->expects($this->once())
            ->method('getUri')
            ->willReturn(GenerisRdf::CLASS_GENERIS_FILE);

        $fileDestinationPropertyValue
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('fileDestinationPropertyValueUri');

        $oldFile = $this->createMock(File::class);

        $this->fileReferenceSerializer
            ->expects($this->once())
            ->method('unserializeFile')
            ->with('fileDestinationPropertyValueUri')
            ->willReturn($oldFile);

        $oldFile
            ->expects($this->once())
            ->method('getFileSystemId')
            ->willReturn('fileSystemId');
        $oldFile
            ->expects($this->once())
            ->method('getBasename')
            ->willReturn('oldFileBasename');
        $directory = $this->createMock(Directory::class);

        $this->fileSystemService
            ->expects($this->once())
            ->method('getDirectory')
            ->with('fileSystemId')
            ->willReturn($directory);

        $newFile = $this->createMock(File::class);

        $directory
            ->expects($this->once())
            ->method('getFile')
            ->willReturn($newFile);

        $oldFile
            ->expects($this->once())
            ->method('readStream');

        $newFile
            ->expects($this->once())
            ->method('write');

        $this->fileReferenceSerializer
            ->expects($this->once())
            ->method('serialize')
            ->with($newFile);

        $toInstance
            ->expects($this->exactly(2))
            ->method('setPropertyValue');

        $this->sut->copy($fromInstance, $toInstance);
    }
}
