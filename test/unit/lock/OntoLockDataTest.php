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
 * Copyright (c) 2015-2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\lock;

use common_exception_InconsistentData;
use core_kernel_classes_Resource;
use oat\tao\model\lock\implementation\OntoLockData;
use PHPUnit\Framework\TestCase;

class OntoLockDataTest extends TestCase
{
    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetOwner(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $owner = $this->createMock(core_kernel_classes_Resource::class);
        $owner
            ->method('getUri')
            ->willReturn('#ownerUri');

        $lock = new OntoLockData($resource, $owner, 'epoch');

        $this->assertInstanceOf(core_kernel_classes_Resource::class, $lock->getOwner());
        $this->assertEquals('#ownerUri', $lock->getOwner()->getUri());
    }

    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testToJson(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $resource
            ->method('getUri')
            ->willReturn('#resourceUri');
        $owner = $this->createMock(core_kernel_classes_Resource::class);
        $owner
            ->method('getUri')
            ->willReturn('#ownerUri');

        $lock = new OntoLockData($resource, $owner, 'epoch');

        $expected = json_encode([
            'resource' => '#resourceUri',
            'owner' =>  '#ownerUri',
            'epoch' => 'epoch',
        ]);

        $this->assertEquals($expected, $lock->toJson());
    }

    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetLockDataException(): void
    {
        $this->expectException(common_exception_InconsistentData::class);
        OntoLockData::getLockData(json_encode([]));
    }
}
