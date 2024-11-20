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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Business\Specification;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\GenerisRdf;
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Specification\EditableListClassSpecification;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\Specification\ClassSpecificationInterface;

class EditableListClassSpecificationTest extends TestCase
{
    /** @var core_kernel_classes_Class|MockObject */
    private core_kernel_classes_Class $class;

    /** @var core_kernel_classes_Property|MockObject */
    private core_kernel_classes_Property $property;

    /** @var ClassSpecificationInterface|MockObject */
    private ClassSpecificationInterface $listClassSpecification;

    /** @var Ontology|MockObject */
    private Ontology $ontology;

    private EditableListClassSpecification $sut;

    protected function setUp(): void
    {
        $this->class = $this->createMock(core_kernel_classes_Class::class);
        $this->property = $this->createMock(core_kernel_classes_Property::class);

        $this->listClassSpecification = $this->createMock(ClassSpecificationInterface::class);
        $this->ontology = $this->createMock(Ontology::class);

        $this->sut = new EditableListClassSpecification($this->listClassSpecification, $this->ontology);
    }

    public function testIsSatisfiedByValidWithoutIsEditablePropertyValue(): void
    {
        $this->listClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($this->property);

        $this->class
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn(null);

        $this->assertTrue($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByValidWithIsEditablePropertyValue(): void
    {
        $this->listClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($this->property);

        $isEditable = $this->createMock(core_kernel_classes_Resource::class);

        $this->class
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn($isEditable);

        $isEditable
            ->expects($this->once())
            ->method('getUri')
            ->willReturn(GenerisRdf::GENERIS_TRUE);

        $this->assertTrue($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByNotValidWithIsEditablePropertyValue(): void
    {
        $this->listClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($this->property);

        $isEditable = $this->createMock(core_kernel_classes_Resource::class);

        $this->class
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn($isEditable);

        $isEditable
            ->expects($this->once())
            ->method('getUri')
            ->willReturn(GenerisRdf::GENERIS_FALSE);

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByWithNotAListClass(): void
    {
        $this->listClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->ontology
            ->expects($this->never())
            ->method($this->anything());

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }
}
