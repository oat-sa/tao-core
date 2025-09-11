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
 * Copyright (c) 2015-2025 Open Assessment Technologies S.A.
 *
 * @author Lionel Lecaque
 */

declare(strict_types=1);

namespace oat\tao\test\unit\lock;

use common_exception_Error;
use core_kernel_classes_Literal;
use core_kernel_classes_Resource;
use oat\tao\model\lock\implementation\SimpleLock;
use PHPUnit\Framework\TestCase;

class SimpleLockTest extends TestCase
{
    public function testConstructException(): void
    {
        $this->expectException(common_exception_Error::class);

        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $owner = $this->createMock(core_kernel_classes_Literal::class);

        new SimpleLock($resource, $owner, 'epoch');
    }

    public function testConstruct(): SimpleLock
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $owner = $this->createMock(core_kernel_classes_Resource::class);
        $owner
            ->method('getUri')
            ->willReturn('#ownerUri');

        $lock = new SimpleLock($resource, $owner, 'epoch');

        $this->assertTrue(true);

        return $lock;
    }

    /**
     * @depends testConstruct
     */
    public function testGetOwnerId(SimpleLock $lock): void
    {
        $this->assertEquals('#ownerUri', $lock->getOwnerId());
    }

    /**
     * @depends testConstruct
     */
    public function testGetCreationTime(SimpleLock $lock): void
    {
        $this->assertEquals('epoch', $lock->getCreationTime());
    }
}
