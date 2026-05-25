<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\taskQueue\Telemetry;

use oat\tao\model\taskQueue\QueueInterface;
use oat\tao\model\taskQueue\Task\CallbackTask;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\Telemetry\TaskQueueTelemetry;
use oat\tao\test\Asset\CallableFixture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaskQueueTelemetryTest extends TestCase
{
    private QueueInterface|MockObject $queue;

    protected function setUp(): void
    {
        $this->queue = $this->createMock(QueueInterface::class);
        $this->queue->method('getName')->willReturn('test-queue');
    }

    public function testIsEnabledWithoutExtension(): void
    {
        if (extension_loaded('opentelemetry')) {
            $this->markTestSkipped('Requires opentelemetry extension to be absent');
        }

        $this->assertFalse(TaskQueueTelemetry::isEnabled());
    }

    public function testTraceEnqueuePassthroughWhenDisabled(): void
    {
        if (extension_loaded('opentelemetry')) {
            putenv('OTEL_PHP_AUTOLOAD_ENABLED=false');
        }

        $task = $this->createMock(TaskInterface::class);
        $task->method('getId')->willReturn('task-1');

        $this->queue->expects($this->never())->method('getName');

        $result = TaskQueueTelemetry::traceEnqueue(
            $this->queue,
            $task,
            static fn (): bool => true
        );

        $this->assertTrue($result);
    }

    public function testTraceProcessTaskPassthroughWhenDisabled(): void
    {
        if (extension_loaded('opentelemetry')) {
            putenv('OTEL_PHP_AUTOLOAD_ENABLED=false');
        }

        $task = $this->createMock(TaskInterface::class);

        $status = TaskQueueTelemetry::traceProcessTask(
            $task,
            static fn (): string => 'finished'
        );

        $this->assertSame('finished', $status);
    }

    public function testResolveTaskClassNameForCallbackTask(): void
    {
        $task = new CallbackTask('id-1', 'owner');
        $task->setCallable([CallableFixture::class, 'exampleStatic']);

        $this->assertSame(
            CallableFixture::class . '::exampleStatic',
            TaskQueueTelemetry::resolveTaskClassName($task)
        );
    }

    public function testResolveTaskClassNameForStringCallable(): void
    {
        $task = $this->createMock(CallbackTaskInterface::class);
        $task->method('getCallable')->willReturn('my_callable');

        $this->assertSame('my_callable', TaskQueueTelemetry::resolveTaskClassName($task));
    }

    public function testResolveTaskClassNameForConcreteTask(): void
    {
        $task = new CallbackTask('id-2', 'owner');

        $this->assertSame(CallbackTask::class, TaskQueueTelemetry::resolveTaskClassName($task));
    }
}
