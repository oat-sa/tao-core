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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\taskQueue\Telemetry;

use oat\tao\model\taskQueue\QueueInterface;
use oat\tao\model\taskQueue\Task\CallbackTask;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use common_report_Report as Report;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\taskQueue\Telemetry\TaskQueueTelemetry;
use oat\tao\test\Asset\CallableFixture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TaskQueueTelemetryTest extends TestCase
{
    private QueueInterface|MockObject $queue;

    private bool $otelAutoloadEnvModified = false;

    private string|false $otelAutoloadOriginalValue = false;

    private bool $otelAutoloadOriginalExists = false;

    protected function setUp(): void
    {
        $this->queue = $this->createMock(QueueInterface::class);
        $this->queue->method('getName')->willReturn('test-queue');
    }

    protected function tearDown(): void
    {
        $this->restoreOtelAutoloadEnv();

        parent::tearDown();
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
            $this->disableOtelAutoload();
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
            $this->disableOtelAutoload();
        }

        $task = $this->createMock(TaskInterface::class);

        $outcome = TaskQueueTelemetry::traceProcessTask(
            $task,
            static fn (): array => ['status' => 'finished', 'report' => null]
        );

        $this->assertSame('finished', $outcome['status']);
        $this->assertNull($outcome['report']);
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

    public function testExtractFailureSummaryFromNestedCommonReport(): void
    {
        $root = Report::createInfo('Running task https://example.test/task');
        $child = Report::createInfo('Starting remote publication creation.');
        $child->add(Report::createFailure('No response from the server, connection cannot be established.'));
        $child->add(Report::createFailure('Publication on remote Deliver error.'));
        $root->add($child);

        $summary = $this->invokeExtractFailureSummaryFromReport($root);

        $this->assertSame(
            'No response from the server, connection cannot be established. | Publication on remote Deliver error.',
            $summary
        );
    }

    public function testSafeAnnotateProcessSpanAcceptsCommonReport(): void
    {
        $report = Report::createInfo('Running task');
        $report->add(Report::createFailure('Task failed'));

        $span = new class () {
            public array $attributes = [];

            public array $status = [];

            public function setAttribute(string $key, mixed $value): void
            {
                $this->attributes[$key] = $value;
            }

            public function setStatus(mixed $code, ?string $description = null): void
            {
                $this->status = [$code, $description];
            }
        };

        $this->invokeSafeAnnotateProcessSpan($span, TaskLogInterface::STATUS_FAILED, $report);

        $this->assertSame(TaskLogInterface::STATUS_FAILED, $span->attributes['task.status']);
        $this->assertSame('Task failed', $span->attributes['task.failure.summary']);
    }

    private function invokeExtractFailureSummaryFromReport(?Report $report): ?string
    {
        $method = (new ReflectionClass(TaskQueueTelemetry::class))->getMethod('extractFailureSummaryFromReport');
        $method->setAccessible(true);

        return $method->invoke(null, $report);
    }

    private function invokeSafeAnnotateProcessSpan(object $span, string $status, ?Report $report): void
    {
        $method = (new ReflectionClass(TaskQueueTelemetry::class))->getMethod('safeAnnotateProcessSpan');
        $method->setAccessible(true);
        $method->invoke(null, $span, $status, $report);
    }

    private function disableOtelAutoload(): void
    {
        $previous = getenv('OTEL_PHP_AUTOLOAD_ENABLED');
        $this->otelAutoloadOriginalExists = $previous !== false;
        $this->otelAutoloadOriginalValue = $previous;
        $this->otelAutoloadEnvModified = true;

        putenv('OTEL_PHP_AUTOLOAD_ENABLED=false');
        $_ENV['OTEL_PHP_AUTOLOAD_ENABLED'] = 'false';
        $_SERVER['OTEL_PHP_AUTOLOAD_ENABLED'] = 'false';
    }

    private function restoreOtelAutoloadEnv(): void
    {
        if (!$this->otelAutoloadEnvModified) {
            return;
        }

        if ($this->otelAutoloadOriginalExists) {
            putenv('OTEL_PHP_AUTOLOAD_ENABLED=' . $this->otelAutoloadOriginalValue);
            $_ENV['OTEL_PHP_AUTOLOAD_ENABLED'] = $this->otelAutoloadOriginalValue;
            $_SERVER['OTEL_PHP_AUTOLOAD_ENABLED'] = $this->otelAutoloadOriginalValue;
        } else {
            putenv('OTEL_PHP_AUTOLOAD_ENABLED');
            unset($_ENV['OTEL_PHP_AUTOLOAD_ENABLED'], $_SERVER['OTEL_PHP_AUTOLOAD_ENABLED']);
        }

        $this->otelAutoloadEnvModified = false;
    }
}
