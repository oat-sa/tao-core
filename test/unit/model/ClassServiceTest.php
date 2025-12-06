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

namespace oat\tao\test\unit\model;

use core_kernel_classes_Resource;
use PHPUnit\Framework\TestCase;
use tao_models_classes_ClassService;

/**
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class ClassServiceTest extends TestCase
{
    public function testDeletePropertyIndex(): void
    {
        $instance = $this->getMockForAbstractClass(
            tao_models_classes_ClassService::class,
            [],
            '',
            false,
            false,
            true,
            []
        );

        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);
        $resourceMock
            ->method('delete')
            ->with(true)
            ->willReturn(true);

        $this->assertTrue($instance->deletePropertyIndex($resourceMock));
    }
}
