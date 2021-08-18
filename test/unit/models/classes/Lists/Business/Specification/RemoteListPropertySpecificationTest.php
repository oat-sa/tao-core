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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Business\Specification;

use oat\generis\test\TestCase;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\Specification\PropertySpecificationInterface;
use oat\tao\model\Lists\Business\Service\RemoteSourcedListOntology;
use oat\tao\model\Lists\Business\Specification\RemoteListPropertySpecification;

class RemoteListPropertySpecificationTest extends TestCase
{
    private const LIST_TYPE_REMOTE = RemoteSourcedListOntology::LIST_TYPE_REMOTE;

    /** @var RemoteListPropertySpecification */
    private $sut;

    /** @var core_kernel_classes_Property|MockObject */
    private $property;

    /** @var core_kernel_classes_Class|MockObject */
    private $range;

    public function testSpecificationInstance(): void
    {
        $this->assertInstanceOf(PropertySpecificationInterface::class, $this->sut);
    }

    public function testIsSatisfiedByValid(): void
    {
        $propertyType = $this->createMock(core_kernel_classes_Property::class);
        $propertyType
            ->method('getUri')
            ->willReturn(self::LIST_TYPE_REMOTE);

        $this->range
            ->method('getOnePropertyValue')
            ->willReturn($propertyType);

        $this->assertEquals(true, $this->sut->isSatisfiedBy($this->property));
    }

    public function testIsSatisfiedByWithoutRange(): void
    {
        $this->property
            ->method('getRange')
            ->willReturn(null);

        $this->assertEquals(false, $this->sut->isSatisfiedBy($this->property));
    }

    public function testIsSatisfiedByWithInvalidParentClass(): void
    {
        $parentClass = $this->createMock(core_kernel_classes_Class::class);
        $parentClass
            ->method('getUri')
            ->willReturn('Invalid parent class URI');

        $this->range
            ->method('getClass')
            ->willReturn($parentClass);

        $this->assertEquals(false, $this->sut->isSatisfiedBy($this->property));
    }

    public function testIsSatisfiedByWithInvalidSubClass(): void
    {
        $this->range
            ->method('isSubClassOf')
            ->willReturn(false);

        $this->assertEquals(false, $this->sut->isSatisfiedBy($this->property));
    }

    public function testIsSatisfiedByWithoutPropertyType(): void
    {
        $this->range
            ->method('getOnePropertyValue')
            ->willReturn(null);

        $this->assertEquals(false, $this->sut->isSatisfiedBy($this->property));
    }

    public function testIsSatisfiedByWithInvalidPropertyType(): void
    {
        $propertyType = $this->createMock(core_kernel_classes_Property::class);
        $propertyType
            ->method('getUri')
            ->willReturn('Invalid property type URI');

        $this->range
            ->method('getOnePropertyValue')
            ->willReturn($propertyType);

        $this->assertEquals(false, $this->sut->isSatisfiedBy($this->property));
    }

    protected function setUp(): void
    {
        $this->sut = new RemoteListPropertySpecification();

        $this->range = $this->createMock(core_kernel_classes_Class::class);
        $this->range
            ->method('getClass')
            ->willReturn($this->createMock(core_kernel_classes_Class::class));
        $this->range
            ->method('isSubClassOf')
            ->willReturn(true);
        $this->range
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));

        $this->property = $this->createMock(core_kernel_classes_Property::class);
        $this->property
            ->method('getUri')
            ->willReturn('propertyUri');
        $this->property
            ->method('getRange')
            ->willReturn($this->range);
    }
}
