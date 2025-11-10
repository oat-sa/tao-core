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

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use oat\tao\model\Lists\Business\Specification\RemoteListClassSpecification;
use oat\tao\model\Specification\ClassSpecificationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\Lists\Business\Specification\RemoteListPropertySpecification;

class RemoteListPropertySpecificationTest extends TestCase
{
    use ServiceManagerMockTrait;

    private RemoteListPropertySpecification $sut;
    private core_kernel_classes_Property|MockObject $property;
    private ClassSpecificationInterface|MockObject $remoteListClassSpecification;

    protected function setUp(): void
    {
        $this->remoteListClassSpecification = $this->createMock(ClassSpecificationInterface::class);
        $this->sut = new RemoteListPropertySpecification();
        $this->sut->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    RemoteListClassSpecification::class => $this->remoteListClassSpecification
                ]
            )
        );
        $this->property = $this->createMock(core_kernel_classes_Property::class);
        $this->property
            ->method('getUri')
            ->willReturn('propertyUri');
    }

    public function testIsSatisfiedByWithoutRange(): void
    {
        $this->property
            ->method('getRange')
            ->willReturn(null);

        $this->assertFalse($this->sut->isSatisfiedBy($this->property));
    }

    public function testIsSatisfiedByWithRange(): void
    {
        $this->property
            ->method('getRange')
            ->willReturn($this->createMock(core_kernel_classes_Class::class));

        $this->remoteListClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->assertTrue($this->sut->isSatisfiedBy($this->property));
    }
}
