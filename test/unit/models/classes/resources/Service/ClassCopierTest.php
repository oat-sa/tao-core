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
 * Copyright (c) 2022-2023 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\resources\Service;

use InvalidArgumentException;
use core_kernel_classes_Class;
use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\resources\ResourceTransferResult;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Resource;
use core_kernel_classes_Property;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\resources\Service\ClassCopier;
use oat\tao\model\resources\Contract\InstanceCopierInterface;
use oat\tao\model\resources\Contract\ClassMetadataCopierInterface;
use oat\tao\model\resources\Contract\ClassMetadataMapperInterface;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;

class ClassCopierTest extends TestCase
{
    private ClassCopier $sut;

    /** @var RootClassesListServiceInterface|MockObject */
    private $rootClassesListService;

    /** @var ClassMetadataCopierInterface|MockObject */
    private $classMetadataCopier;

    /** @var InstanceCopierInterface|MockObject */
    private $instanceCopier;

    /** @var ClassMetadataMapperInterface|MockObject */
    private $classMetadataMapper;

    /** @var Ontology|MockObject */
    private $ontology;

    protected function setUp(): void
    {
        $this->rootClassesListService = $this->createMock(RootClassesListServiceInterface::class);
        $this->classMetadataCopier = $this->createMock(ClassMetadataCopierInterface::class);
        $this->instanceCopier = $this->createMock(ResourceTransferInterface::class);
        $this->classMetadataMapper = $this->createMock(ClassMetadataMapperInterface::class);
        $this->ontology = $this->createMock(Ontology::class);

        $this->sut = new ClassCopier(
            $this->rootClassesListService,
            $this->classMetadataCopier,
            $this->instanceCopier,
            $this->classMetadataMapper,
            $this->ontology
        );
    }

    public function testTransfer(): void
    {
        $destinationClass = $this->createClass('destinationClassUri', 'classLabel');
        $fromClass = $this->createClass('classUri', 'classLabel');

        $this->ontology
            ->method('getClass')
            ->willReturnOnConsecutiveCalls(
                $fromClass,
                $destinationClass
            );

        $this->doCopy($fromClass, $destinationClass);

        $this->assertEquals(
            new ResourceTransferResult('newClassUri'),
            $this->sut->transfer(
                new ResourceTransferCommand(
                    'classUri',
                    'destinationClassUri',
                    ResourceTransferCommand::ACL_KEEP_ORIGINAL,
                    ResourceTransferCommand::TRANSFER_MODE_COPY
                )
            )
        );
    }

    public function testCopy(): void
    {
        $destinationClass = $this->createClass('destinationClassUri', 'classLabel');
        $fromClass = $this->createClass('classUri', 'classLabel');

        $this->assertEquals(
            $this->doCopy($fromClass, $destinationClass),
            $this->sut->copy($fromClass, $destinationClass)
        );
    }

    public function testCopyWithInvalidDestinationClass(): void
    {
        $rootClass = $this->createClass('rootClassUri', 'label');

        $this->rootClassesListService
            ->expects($this->once())
            ->method('list')
            ->willReturn(
                [
                    $rootClass,
                ]
            );

        $class = $this->createClass('classUri', 'label');

        $class->expects($this->once())
            ->method('equals')
            ->willReturn(true);

        $class->expects($this->never())
            ->method('isSubClassOf');

        $destinationClass = $this->createClass('destinationClassUri', 'label');

        $destinationClass->expects($this->once())
            ->method('equals')
            ->willReturn(false);

        $destinationClass->expects($this->once())
            ->method('isSubClassOf')
            ->with($rootClass)
            ->willReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Selected class (classUri) and destination class (destinationClassUri) ' .
            'must be in the same root class (rootClassUri).'
        );

        $this->sut->copy($class, $destinationClass);
    }

    /**
     * @param core_kernel_classes_Class|MockObject $fromClass
     * @param core_kernel_classes_Class|MockObject $destinationClass
     * @return core_kernel_classes_Class|MockObject
     */
    private function doCopy(MockObject $fromClass, MockObject $destinationClass): MockObject
    {
        $classInstance = $this->createInstance('newInstanceUri', 'label');

        $rootClass = $this->createClass('rootClassUri', 'rootClassLabel');
        $subClass = $this->createClass('classUri', 'subClassLabel');
        $newClass = $this->createClass('newClassUri', 'classLabel');
        $newSubClass = $this->createClass('newSubClassUri', 'newSubClassLabel');

        $newClassProperty = $this->createMock(core_kernel_classes_Property::class);
        $newSubClassProperty = $this->createMock(core_kernel_classes_Property::class);

        $newSubClass->expects($this->once())
            ->method('getProperties')
            ->willReturn(
                [
                    $newSubClassProperty,
                ]
            );

        $subClass->expects($this->never())
            ->method('equals');

        $subClass->expects($this->never())
            ->method('isSubClassOf');

        $subClass->expects($this->once())
            ->method('getInstances')
            ->willReturn([]);

        $subClass->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([]);

        $fromClass->expects($this->once())
            ->method('equals')
            ->with($rootClass)
            ->willReturn(true);

        $fromClass->expects($this->once())
            ->method('getInstances')
            ->willReturn(
                [
                    $classInstance,
                ]
            );

        $fromClass->expects($this->never())
            ->method('isSubClassOf');

        $fromClass->expects($this->once())
            ->method('getSubClasses')
            ->willReturn(
                [
                    $subClass,
                ]
            );

        $newClass->expects($this->never())
            ->method('equals');

        $newClass->expects($this->never())
            ->method('isSubClassOf');

        $newClass->expects($this->once())
            ->method('createSubClass')
            ->with('subClassLabel')
            ->willReturn($newSubClass);

        $newClass->expects($this->once())
            ->method('getProperties')
            ->willReturn(
                [
                    $newClassProperty,
                ]
            );

        $destinationClass
            ->expects($this->once())
            ->method('equals')
            ->with($rootClass)
            ->willReturn(true);

        $destinationClass
            ->expects($this->once())
            ->method('createSubClass')
            ->with('classLabel')
            ->willReturn($newClass);

        $this->rootClassesListService
            ->expects($this->atLeastOnce())
            ->method('list')
            ->willReturn([$rootClass]);

        $this->classMetadataCopier
            ->expects($this->exactly(2))
            ->method('copy');

        $this->instanceCopier
            ->expects($this->once())
            ->method('transfer')
            ->with(
                new ResourceTransferCommand(
                    'newInstanceUri',
                    'newClassUri',
                    ResourceTransferCommand::ACL_KEEP_ORIGINAL,
                    ResourceTransferCommand::TRANSFER_MODE_COPY
                )
            );

        $this->classMetadataMapper
            ->expects($this->exactly(2))
            ->method('remove');

        return $newClass;
    }

    /**
     * @return core_kernel_classes_Class|MockObject
     */
    private function createClass(string $uri, string $classLabel)
    {
        $class = $this->createMock(core_kernel_classes_Class::class);

        $class->method('getUri')
            ->willReturn($uri);

        $class->method('getLabel')
            ->willReturn($classLabel);

        return $class;
    }

    /**
     * @return core_kernel_classes_Resource|MockObject
     */
    private function createInstance(string $uri, string $label): core_kernel_classes_Resource
    {
        $instance = $this->createMock(core_kernel_classes_Resource::class);

        $instance->method('getUri')
            ->willReturn($uri);

        $instance->method('getLabel')
            ->willReturn($label);

        return $instance;
    }
}
