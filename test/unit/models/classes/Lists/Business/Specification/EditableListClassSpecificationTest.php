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
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Specification\EditableListClassSpecification;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\Specification\ClassSpecificationInterface;

class EditableListClassSpecificationTest extends TestCase
{
    /** @var EditableListClassSpecification */
    private $sut;

    /** @var ClassSpecificationInterface|MockObject */
    private $languageClassSpecification;

    /** @var ClassSpecificationInterface|MockObject */
    private $listClassSpecification;

    /** @var core_kernel_classes_Class|MockObject */
    private $class;

    protected function setUp(): void
    {
        $this->listClassSpecification = $this->createMock(ClassSpecificationInterface::class);
        $this->languageClassSpecification = $this->createMock(ClassSpecificationInterface::class);

        $this->class = $this->createMock(core_kernel_classes_Class::class);

        $this->sut = new EditableListClassSpecification(
            $this->listClassSpecification, $this->languageClassSpecification
        );
    }

    public function testIsSatisfiedByValid(): void
    {
        $this->listClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->languageClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->assertTrue($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByWithNotAListClass(): void
    {
        $this->listClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->languageClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByWithNotLanguageListClass(): void
    {
        $this->listClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->languageClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }
}
