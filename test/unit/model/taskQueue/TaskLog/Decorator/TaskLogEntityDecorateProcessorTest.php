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
 * Copyright (c) 2024-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\taskQueue\TaskLog\Decorator;

use oat\tao\model\taskQueue\TaskLog\Decorator\TaskLogEntityDecorateProcessor;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\test\unit\model\taskQueue\TaskLog\Decorator\Stub\StubEntityDecorator;
use PHPUnit\Framework\TestCase;

class TaskLogEntityDecorateProcessorTest extends TestCase
{
    private TaskLogEntityDecorateProcessor $subject;

    protected function setUp(): void
    {
        $this->subject = new TaskLogEntityDecorateProcessor();
    }

    public function testDecorated(): void
    {
        $entityMock = $this->createMock(EntityInterface::class);
        $entityMock->expects(self::once())
            ->method('toArray')
            ->willReturn(['testValue' => 'value']);
        $this->subject->setEntity($entityMock);
        $this->subject->addDecorator(StubEntityDecorator::class);

        $result = $this->subject->toArray();

        $this->assertIsArray($result);
        $this->assertEquals('decoratedValue', $result['testValue']);
    }

    public function testNotDecorated(): void
    {
        $entityMock = $this->createMock(EntityInterface::class);
        $entityMock->expects(self::once())
            ->method('toArray')
            ->willReturn(['testValue' => 'value']);
        $this->subject->setEntity($entityMock);

        $result = $this->subject->toArray();

        $this->assertIsArray($result);
        $this->assertEquals('value', $result['testValue']);
    }
}
