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

namespace oat\tao\test\unit\model\Lists\DataAccess\Repository;

use core_kernel_classes_Class;
use core_kernel_classes_ContainerCollection;
use core_kernel_classes_Property;
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertyCollection;
use oat\tao\model\Lists\DataAccess\Repository\DependsOnPropertyRepository;

class DependsOnPropertyRepositoryTest extends TestCase
{
    /** @var DependsOnPropertyRepository */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new DependsOnPropertyRepository();
    }

    public function testFindAll(): void
    {
        $collection = new DependsOnPropertyCollection();
        $property = $this->createMock(core_kernel_classes_Property::class);
        $class = $this->createMock(core_kernel_classes_Class::class);

        $domainCollection = $this->createMock(core_kernel_classes_ContainerCollection::class);

        $property->method('getDomain')
            ->willReturn($domainCollection);

        $domainCollection->method('get')
            ->willReturn($class);

        $this->sut->withProperties([]);

        $this->assertEquals(
            $collection,
            $this->sut->findAll(
                [
                    'property' => $property,
                ]
            )
        );
    }
}
