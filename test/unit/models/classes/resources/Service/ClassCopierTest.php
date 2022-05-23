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

use InvalidArgumentException;
use core_kernel_classes_Class;
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
    /** @var ClassCopier */
    private $sut;

    /** @var RootClassesListServiceInterface|MockObject */
    private $rootClassesListService;

    /** @var ClassMetadataCopierInterface|MockObject */
    private $classMetadataCopier;

    /** @var InstanceCopierInterface|MockObject */
    private $instanceCopier;

    /** @var ClassMetadataMapperInterface|MockObject */
    private $classMetadataMapper;

    protected function setUp(): void
    {
        $this->rootClassesListService = $this->createMock(RootClassesListServiceInterface::class);
        $this->classMetadataCopier = $this->createMock(ClassMetadataCopierInterface::class);
        $this->instanceCopier = $this->createMock(InstanceCopierInterface::class);
        $this->classMetadataMapper = $this->createMock(ClassMetadataMapperInterface::class);

        $this->sut = new ClassCopier(
            $this->rootClassesListService,
            $this->classMetadataCopier,
            $this->instanceCopier,
            $this->classMetadataMapper
        );
    }

    public function testCopy(): void
    {
        $rootClass = $this->createMock(core_kernel_classes_Class::class);

        $this->rootClassesListService
            ->expects($this->atLeastOnce())
            ->method('list')
            ->willReturn([$rootClass]);

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classUri');
        $class
            ->expects($this->once())
            ->method('equals')
            ->with($rootClass)
            ->willReturn(true);
        $class
            ->expects($this->never())
            ->method('isSubClassOf');

        $destinationClass = $this->createMock(core_kernel_classes_Class::class);
        $destinationClass
            ->expects($this->once())
            ->method('equals')
            ->with($rootClass)
            ->willReturn(true);
        $destinationClass
            ->expects($this->never())
            ->method('isSubClassOf');
        $destinationClass
            ->expects($this->never())
            ->method('getUri');

        $class
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('classLabel');

        $newClass = $this->createMock(core_kernel_classes_Class::class);
        $newClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('newClassUri');

        $destinationClass
            ->expects($this->once())
            ->method('createSubClass')
            ->with('classLabel')
            ->willReturn($newClass);

        $this->classMetadataCopier
            ->expects($this->at(0))
            ->method('copy')
            ->with($class, $newClass);

        $classInstance = $this->createMock(core_kernel_classes_Resource::class);

        $class
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn(
                [
                    $classInstance,
                ]
            );

        $this->instanceCopier
            ->expects($this->once())
            ->method('copy')
            ->with($classInstance, $newClass);

        $subClass = $this->createMock(core_kernel_classes_Class::class);

        $class
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn(
                [
                    $subClass,
                ]
            );

        $subClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classUri');
        $subClass
            ->expects($this->never())
            ->method('equals');
        $subClass
            ->expects($this->never())
            ->method('isSubClassOf');

        $newClass
            ->expects($this->never())
            ->method('equals');
        $newClass
            ->expects($this->never())
            ->method('isSubClassOf');

        $subClass
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('subClassLabel');

        $newSubClass = $this->createMock(core_kernel_classes_Class::class);
        $newSubClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('newSubClassUri');

        $newClass
            ->expects($this->once())
            ->method('createSubClass')
            ->with('subClassLabel')
            ->willReturn($newSubClass);

        $this->classMetadataCopier
            ->expects($this->at(1))
            ->method('copy')
            ->with($subClass, $newSubClass);

        $subClass
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([]);
        $subClass
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([]);

        $newClassProperty = $this->createMock(core_kernel_classes_Property::class);

        $newClass
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn(
                [
                    $newClassProperty,
                ]
            );

        $this->classMetadataMapper
            ->expects($this->at(0))
            ->method('remove')
            ->with(
                [
                    $newClassProperty,
                ]
            );

        $newSubClassProperty = $this->createMock(core_kernel_classes_Property::class);

        $newSubClass
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn(
                [
                    $newSubClassProperty,
                ]
            );

        $this->classMetadataMapper
            ->expects($this->at(1))
            ->method('remove')
            ->with(
                [
                    $newSubClassProperty,
                ]
            );

        $this->assertEquals($newClass, $this->sut->copy($class, $destinationClass));
    }

    public function testCopyWithInvalidDestinationClass(): void
    {
        $rootClass = $this->createMock(core_kernel_classes_Class::class);
        $rootClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('rootClassUri');

        $this->rootClassesListService
            ->expects($this->once())
            ->method('list')
            ->willReturn(
                [
                    $rootClass,
                ]
            );

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('classUri');
        $class
            ->expects($this->once())
            ->method('equals')
            ->willReturn(true);
        $class
            ->expects($this->never())
            ->method('isSubClassOf');

        $destinationClass = $this->createMock(core_kernel_classes_Class::class);
        $destinationClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('destinationClassUri');
        $destinationClass
            ->expects($this->once())
            ->method('equals')
            ->willReturn(false);
        $destinationClass
            ->expects($this->once())
            ->method('isSubClassOf')
            ->with($rootClass)
            ->willReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Selected class (classUri) and destination class (destinationClassUri) must be in the same root class (rootClassUri).'
        );

        $this->sut->copy($class, $destinationClass);
    }
}
