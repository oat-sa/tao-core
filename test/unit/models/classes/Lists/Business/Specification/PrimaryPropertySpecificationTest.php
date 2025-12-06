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

use core_kernel_classes_Property;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Business\Contract\DependentPropertiesRepositoryInterface;
use oat\tao\model\Lists\Business\Specification\PrimaryPropertySpecification;

class PrimaryPropertySpecificationTest extends TestCase
{
    /** @var PrimaryPropertySpecification */
    private $sut;

    /** @var DependentPropertiesRepositoryInterface */
    private $dependentPropertiesRepository;

    protected function setUp(): void
    {
        $this->dependentPropertiesRepository = $this->createMock(DependentPropertiesRepositoryInterface::class);

        $this->sut = new PrimaryPropertySpecification(
            $this->dependentPropertiesRepository
        );
    }

    public function testIsSatisfiedBy(): void
    {
        $this->dependentPropertiesRepository
            ->method('findTotalChildren')
            ->willReturn(1);

        $property = $this->createMock(core_kernel_classes_Property::class);

        $this->assertTrue($this->sut->isSatisfiedBy($property));
    }

    public function testIsNotSatisfiedBy(): void
    {
        $this->dependentPropertiesRepository
            ->method('findTotalChildren')
            ->willReturn(0);

        $property = $this->createMock(core_kernel_classes_Property::class);

        $this->assertFalse($this->sut->isSatisfiedBy($property));
    }
}
