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

use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyRdfs;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\resources\Service\ClassMetadataCopier;
use oat\tao\model\resources\Contract\ClassMetadataMapperInterface;

class ClassMetadataCopierTest extends TestCase
{
    /** @var ClassMetadataCopier */
    private $sut;

    /** @var ClassMetadataMapperInterface|MockObject */
    private $classMetadataMapper;

    /** @var core_kernel_classes_Property|MockObject */
    private $domainProperty;

    protected function setUp(): void
    {
        $this->classMetadataMapper = $this->createMock(ClassMetadataMapperInterface::class);
        $this->domainProperty = $this->createMock(core_kernel_classes_Property::class);

        $this->sut = new ClassMetadataCopier($this->classMetadataMapper);
    }

    public function testCopy(): void
    {
        $classProperty1 = $this->createMock(core_kernel_classes_Property::class);
        $classProperty2 = $this->createMock(core_kernel_classes_Property::class);

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->once())
            ->method('getProperties')
            ->with(true)
            ->willReturn(
                [
                    'classProperty1' => $classProperty1,
                    'classProperty2' => $classProperty2,
                ]
            );

        $destinationClass = $this->createMock(core_kernel_classes_Class::class);
        $destinationClass
            ->expects($this->once())
            ->method('getProperties')
            ->with(true)
            ->willReturn([]);

        $this->copyProperty($classProperty1, $destinationClass, 'newPropertyUri1');
        $this->copyProperty($classProperty2, $destinationClass, 'newPropertyUri2');

        $this->classMetadataMapper
            ->expects($this->exactly(2))
            ->method('add');

        $this->sut->copy($class, $destinationClass);
    }

    /**
     * @param core_kernel_classes_Property|MockObject $property
     * @param core_kernel_classes_Class|MockObject $destinationClass
     */
    private function copyProperty(
        core_kernel_classes_Property $property,
        core_kernel_classes_Class $destinationClass,
        string $newPropertyUri
    ): void {
        $newPropertyResource = $this->createMock(core_kernel_classes_Resource::class);
        $newPropertyResource
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($newPropertyUri);

        $property
            ->expects($this->once())
            ->method('duplicate')
            ->willReturn($newPropertyResource);

        $newProperty = $this->createMock(core_kernel_classes_Property::class);

        $newPropertyResource
            ->expects($this->once())
            ->method('getProperty')
            ->with($newPropertyUri)
            ->willReturn($newProperty);

        $newProperty
            ->expects($this->once())
            ->method('getProperty')
            ->with(OntologyRdfs::RDFS_DOMAIN)
            ->willReturn($this->domainProperty);
        $newProperty
            ->expects($this->once())
            ->method('removePropertyValues')
            ->with($this->domainProperty);
        $newProperty
            ->expects($this->once())
            ->method('setDomain')
            ->with($destinationClass);
    }
}
