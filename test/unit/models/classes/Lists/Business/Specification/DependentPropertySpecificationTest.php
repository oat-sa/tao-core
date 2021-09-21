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

use oat\generis\model\resource\DependsOnPropertyCollection;
use oat\generis\test\TestCase;
use core_kernel_classes_Property;
use oat\tao\model\Specification\PropertySpecificationInterface;
use oat\tao\model\Lists\Business\Specification\DependentPropertySpecification;

class DependentPropertySpecificationTest extends TestCase
{
    /** @var DependentPropertySpecification */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new DependentPropertySpecification();
    }

    public function testSpecificationInstance(): void
    {
        $this->assertInstanceOf(PropertySpecificationInterface::class, $this->sut);
    }

    /**
     * @dataProvider getTestData
     */
    public function testIsSatisfiedBy(?core_kernel_classes_Property $property, bool $expected): void
    {
        $collection = $this->createMock(DependsOnPropertyCollection::class);
        $collection->method('current')
            ->willReturn($property);

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property->method('getDependsOnPropertyCollection')
            ->willReturn($collection);

        $this->assertEquals($expected, $this->sut->isSatisfiedBy($property));
    }

    public function getTestData(): array
    {
        return [
            'No value' => [
                'property' => null,
                'expected' => false,
            ],
            'Any value' => [
                'property' => $this->createMock(core_kernel_classes_Property::class),
                'expected' => true,
            ],
        ];
    }
}
