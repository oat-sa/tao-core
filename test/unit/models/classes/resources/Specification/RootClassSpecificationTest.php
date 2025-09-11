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

namespace oat\tao\test\unit\model\resources\Specification;

use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use oat\tao\model\resources\Specification\RootClassSpecification;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;

class RootClassSpecificationTest extends TestCase
{
    /** @var RootClassSpecification */
    private $sut;

    protected function setUp(): void
    {
        $rootClassesListService = $this->createMock(RootClassesListServiceInterface::class);
        $rootClassesListService
            ->method('listUris')
            ->willReturn(['rootUri']);

        $this->sut = new RootClassSpecification($rootClassesListService);
    }

    public function testIsSatisfiedBy(): void
    {
        $class = $this->createClassMock('uri');
        $this->assertFalse($this->sut->isSatisfiedBy($class));

        $rootClass = $this->createClassMock('rootUri');
        $this->assertTrue($this->sut->isSatisfiedBy($rootClass));
    }

    private function createClassMock(string $uri): core_kernel_classes_Class
    {
        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        return $class;
    }
}
