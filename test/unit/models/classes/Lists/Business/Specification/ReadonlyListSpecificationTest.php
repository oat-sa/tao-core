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

use core_kernel_classes_Class;
use oat\generis\test\TestCase;
use core_kernel_classes_Property;
use oat\tao\model\Lists\Business\Specification\ReadonlyListSpecification;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\Specification\ClassSpecificationInterface;
use oat\tao\model\Lists\Business\Service\RemoteSourcedListOntology;
use oat\tao\model\Lists\Business\Specification\ListClassSpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListClassSpecification;

class ReadonlyListSpecificationTest extends TestCase
{
    private const LIST_TYPE_REMOTE = TaoOntology::CLASS_URI_READONLY_LIST;


    private ReadonlyListSpecification $sut;

    /** @var core_kernel_classes_Class|MockObject */
    private $class;

    protected function setUp(): void
    {
        $this->sut = new ReadonlyListSpecification();
        $this->class = $this->createMock(core_kernel_classes_Class::class);
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

    public function testIsSatisfiedByInvalid(): void
    {
        $propertyType = $this->createMock(core_kernel_classes_Property::class);
        $propertyType
            ->method('getUri')
            ->willReturn('someOtherType');

        $this->class
            ->method('getOnePropertyValue')
            ->willReturn($propertyType);

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }
}
