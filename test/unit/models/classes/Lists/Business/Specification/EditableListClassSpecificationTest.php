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

namespace oat\tao\test\unit\models\classes\Lists\Business\Specification;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\GenerisRdf;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Business\Specification\EditableListClassSpecification;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\Specification\ClassSpecificationInterface;

class EditableListClassSpecificationTest extends TestCase
{
    /** @var core_kernel_classes_Class|MockObject */
    private core_kernel_classes_Class $class;

    /** @var ClassSpecificationInterface|MockObject */
    private ClassSpecificationInterface $listClassSpecification;

    private EditableListClassSpecification $sut;

    protected function setUp(): void
    {
        $this->class = $this->createMock(core_kernel_classes_Class::class);

        $this->listClassSpecification = $this->createMock(ClassSpecificationInterface::class);

        $this->sut = new EditableListClassSpecification($this->listClassSpecification);
    }

    public function testIsSatisfiedByValid(): void
    {
        $this->listClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->class
            ->expects($this->once())
            ->method('isWritable')
            ->willReturn(true);

        $this->assertTrue($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByNotAListClass(): void
    {
        $this->listClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->class
            ->expects($this->never())
            ->method('isWritable');

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByNotWritable(): void
    {
        $this->listClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->class
            ->expects($this->once())
            ->method('isWritable')
            ->willReturn(false);

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }
}
