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

namespace oat\test\unit\model;

use oat\generis\test\TestCase;
use core_kernel_classes_Resource;
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

        $resourceProphet = $this->prophesize(core_kernel_classes_Resource::class);
        $resourceProphet
            ->delete(true)
            ->willReturn($resourceProphet);
        $resourceMock = $resourceProphet->reveal();

        $this->assertSame($resourceMock, $instance->deletePropertyIndex($resourceMock));
    }
}
