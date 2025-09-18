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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\test\unit\model\taskQueue\Event;

use PHPUnit\Framework\TestCase;
use oat\tao\model\taskQueue\Event\TaskLogArchivedEvent;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;

class TaskLogArchivedEventTest extends TestCase
{
    /** @var EntityInterface */
    private $entityMock;

    /** @var EntityInterface */
    private $forced = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityMock = $this->createMock(EntityInterface::class);
    }

    public function testGetTaskLogEntity()
    {
        $this->assertSame($this->entityMock, $this->createTestInstance()->getTaskLogEntity());
    }

    public function testIsForced()
    {
        $this->forced = true;

        $this->assertTrue($this->createTestInstance()->isForced());
    }

    public function testGetName()
    {
        $this->assertEquals(TaskLogArchivedEvent::class, $this->createTestInstance()->getName());
    }

    /**
     * @return TaskLogArchivedEvent
     */
    private function createTestInstance()
    {
        return new TaskLogArchivedEvent($this->entityMock, $this->forced);
    }
}
