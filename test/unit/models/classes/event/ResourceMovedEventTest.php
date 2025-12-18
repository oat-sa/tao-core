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

namespace oat\tao\test\unit\model\event;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use oat\tao\model\event\ResourceMovedEvent;

class ResourceMovedEventTest extends TestCase
{
    /** @var core_kernel_classes_Resource|MockObject */
    private $resourceMock;

    /** @var core_kernel_classes_Class|MockObject */
    private $classMock;

    /** @var ResourceMovedEvent */
    private $subject;

    protected function setUp(): void
    {
        $this->resourceMock  = $this->createMock(core_kernel_classes_Resource::class);
        $this->classMock = $this->createMock(core_kernel_classes_Class::class);
        $this->subject = new ResourceMovedEvent(
            $this->resourceMock,
            $this->classMock
        );
    }

    public function testGetters()
    {
        $this->assertSame($this->resourceMock, $this->subject->getMovedResource());
        $this->assertSame($this->classMock, $this->subject->getDestinationClass());
    }
}
