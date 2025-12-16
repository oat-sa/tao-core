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
 * Copyright (c) 2021-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Lists\Business\Specification;

use core_kernel_classes_Class;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Property;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\Specification\ClassSpecificationInterface;
use oat\tao\model\Lists\Business\Service\RemoteSourcedListOntology;
use oat\tao\model\Lists\Business\Specification\ListClassSpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListClassSpecification;

class RemoteListClassSpecificationTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const LIST_TYPE_REMOTE = RemoteSourcedListOntology::LIST_TYPE_REMOTE;

    private RemoteListClassSpecification $sut;
    private core_kernel_classes_Class|MockObject $class;
    private ClassSpecificationInterface|MockObject $listClassSpecification;

    protected function setUp(): void
    {
        $this->listClassSpecification = $this->createMock(ClassSpecificationInterface::class);
        $this->listClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->sut = new RemoteListClassSpecification();
        $this->sut->setServiceManager(
            $this->getServiceManagerMock(
                [
                    ListClassSpecification::class => $this->listClassSpecification,
                ]
            )
        );

        $this->class = $this->createMock(core_kernel_classes_Class::class);
        $this->class
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));
    }

    public function testIsSatisfiedByValid(): void
    {
        $propertyType = $this->createMock(core_kernel_classes_Property::class);
        $propertyType
            ->method('getUri')
            ->willReturn(self::LIST_TYPE_REMOTE);

        $this->class
            ->method('getOnePropertyValue')
            ->willReturn($propertyType);

        $this->assertTrue($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByWithInvalidParentClass(): void
    {
        $parentClass = $this->createMock(core_kernel_classes_Class::class);
        $parentClass
            ->method('getUri')
            ->willReturn('Invalid parent class URI');

        $this->class
            ->method('getClass')
            ->willReturn($parentClass);

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByWithInvalidSubClass(): void
    {
        $this->listClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByWithoutPropertyType(): void
    {
        $this->class
            ->method('getOnePropertyValue')
            ->willReturn(null);

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByWithInvalidPropertyType(): void
    {
        $propertyType = $this->createMock(core_kernel_classes_Property::class);
        $propertyType
            ->method('getUri')
            ->willReturn('Invalid property type URI');

        $this->class
            ->method('getOnePropertyValue')
            ->willReturn($propertyType);

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }
}
