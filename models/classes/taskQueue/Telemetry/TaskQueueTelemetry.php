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

namespace oat\tao\model\taskQueue\Telemetry;

use common_report_Report;
use oat\oatbox\reporting\ReportInterface;
use oat\tao\model\taskQueue\QueueInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLogInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SDK\Sdk;
use Throwable;

/**
 * Propagates W3C trace context through task queue metadata and creates enqueue/process spans.
 *
 * Must not throw and must not alter operation results.
 */
final class TaskQueueTelemetry
{
    public const string METADATA_TRACE_CONTEXT_KEY = '__otel_trace_context__';
    private const string TRACER_NAME = 'tao-task-queue';
    private const string ERROR_STATUS_PROCESS = 'task processing error';
    private const int FAILURE_SUMMARY_MAX_LENGTH = 500;

    public static function isEnabled(): bool
    {
        if (extension_loaded('opentelemetry') === false) {
            return false;
        }

        if (Sdk::isInstrumentationDisabled('tao-task-queue') === true) {
            return false;
        }

        return filter_var(getenv('OTEL_PHP_AUTOLOAD_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param callable(): mixed $enqueue
     */
    public static function traceEnqueue(QueueInterface $queue, TaskInterface $task, callable $enqueue): mixed
    {
        if (!self::isEnabled()) {
            return $enqueue();
        }

        return self::runInSpan(
            static fn (): array => self::startEnqueueSpan($queue, $task),
            static function () use ($task, $enqueue) {
                self::safeInjectTraceContextIntoTask($task);

                return $enqueue();
            },
            static function ($span, mixed $result): void {
                self::safeSetSpanOk($span);
            }
        );
    }

    /**
     * @param callable(): array{status: string, report: ?ReportInterface} $processor
     *
     * @return array{status: string, report: ?ReportInterface}
     */
    public static function traceProcessTask(TaskInterface $task, callable $processor): array
    {
        if (!self::isEnabled()) {
            return $processor();
        }

        return self::runInSpan(
            static fn (): array => self::startProcessSpan($task),
            $processor,
            static function ($span, array $outcome): void {
                self::safeAnnotateProcessSpan(
                    $span,
                    (string) ($outcome['status'] ?? ''),
                    $outcome['report'] ?? null
                );
            }
        );
    }

    public static function resolveTaskClassName(TaskInterface $task): string
    {
        if ($task instanceof CallbackTaskInterface) {
            $callable = $task->getCallable();

            if (is_string($callable)) {
                return $callable;
            }

            if (is_array($callable)) {
                $class = is_object($callable[0]) ? get_class($callable[0]) : (string) $callable[0];

                return $class . '::' . $callable[1];
            }

            if ($callable instanceof \Closure) {
                return 'closure';
            }

            if (is_object($callable)) {
                return get_class($callable);
            }
        }

        return get_class($task);
    }

    /**
     * @param callable(): array{0: object|null, 1: ScopeInterface|null} $startSpan
     * @param callable(): mixed $operation
     * @param callable(object|null, mixed): void|null $annotate
     */
    private static function runInSpan(callable $startSpan, callable $operation, ?callable $annotate = null): mixed
    {
        $span = null;
        $scope = null;

        try {
            [$span, $scope] = $startSpan();
        } catch (Throwable) {
            return $operation();
        }

        try {
            $result = $operation();

            if ($annotate !== null) {
                try {
                    $annotate($span, $result);
                } catch (Throwable) {
                }
            }

            return $result;
        } finally {
            self::safeEndSpan($span, $scope);
        }
    }

    /**
     * @return array{0: object|null, 1: ScopeInterface|null}
     */
    private static function startEnqueueSpan(QueueInterface $queue, TaskInterface $task): array
    {
        $taskClass = self::resolveTaskClassName($task);
        $tracer = Globals::tracerProvider()->getTracer(self::TRACER_NAME);

        $span = $tracer
            ->spanBuilder(sprintf('ENQUEUE %s', $taskClass))
            ->setSpanKind(SpanKind::KIND_PRODUCER)
            ->setAttribute('messaging.system', 'tao_task_queue')
            ->setAttribute('messaging.operation', 'publish')
            ->setAttribute('messaging.destination.name', $queue->getName())
            ->setAttribute('task.class', $taskClass)
            ->setAttribute('task.id', $task->getId())
            ->startSpan();

        return [$span, $span->activate()];
    }

    /**
     * @return array{0: object|null, 1: ScopeInterface|null}
     */
    private static function startProcessSpan(TaskInterface $task): array
    {
        $taskClass = self::resolveTaskClassName($task);
        $tracer = Globals::tracerProvider()->getTracer(self::TRACER_NAME);
        $parentContext = self::extractContextFromTask($task);

        $spanBuilder = $tracer
            ->spanBuilder(sprintf('PROCESS %s', $taskClass))
            ->setSpanKind(SpanKind::KIND_CONSUMER)
            ->setAttribute('messaging.system', 'tao_task_queue')
            ->setAttribute('messaging.operation', 'process')
            ->setAttribute('task.class', $taskClass)
            ->setAttribute('task.id', $task->getId());

        if ($task->getMetadata(self::METADATA_TRACE_CONTEXT_KEY)) {
            $spanBuilder = $spanBuilder->setParent($parentContext);
        }

        $span = $spanBuilder->startSpan();

        return [$span, $span->activate()];
    }

    private static function safeInjectTraceContextIntoTask(TaskInterface $task): void
    {
        try {
            $carrier = [];
            TraceContextPropagator::getInstance()->inject($carrier);

            if ($carrier === []) {
                return;
            }

            $task->setMetadata(self::METADATA_TRACE_CONTEXT_KEY, $carrier);
        } catch (Throwable) {
        }
    }

    private static function extractContextFromTask(TaskInterface $task): Context
    {
        $carrier = $task->getMetadata(self::METADATA_TRACE_CONTEXT_KEY, []);

        if (!is_array($carrier) || $carrier === []) {
            return Context::getCurrent();
        }

        return TraceContextPropagator::getInstance()->extract($carrier);
    }

    private static function safeAnnotateProcessSpan($span, string $status, ?ReportInterface $report): void
    {
        if ($span === null) {
            return;
        }

        try {
            $span->setAttribute('task.status', $status);

            if ($status !== TaskLogInterface::STATUS_FAILED) {
                $span->setStatus(StatusCode::STATUS_OK);

                return;
            }

            $span->setStatus(StatusCode::STATUS_ERROR, self::ERROR_STATUS_PROCESS);

            $failureSummary = self::extractFailureSummaryFromReport($report);

            if ($failureSummary !== null) {
                $span->setAttribute('task.failure.summary', self::sanitizeFailureSummary($failureSummary));
            }
        } catch (Throwable) {
        }
    }

    private static function extractFailureSummaryFromReport(?ReportInterface $report): ?string
    {
        if ($report === null || !$report instanceof common_report_Report) {
            return null;
        }

        if ($report->getType() !== ReportInterface::TYPE_ERROR && !$report->containsError()) {
            return null;
        }

        $messages = [];

        foreach ($report->getErrors(true) as $errorReport) {
            $message = trim($errorReport->getMessage());

            if ($message !== '') {
                $messages[] = $message;
            }
        }

        if ($report->getType() === ReportInterface::TYPE_ERROR) {
            $rootMessage = trim($report->getMessage());

            if ($rootMessage !== '' && !in_array($rootMessage, $messages, true)) {
                array_unshift($messages, $rootMessage);
            }
        }

        if ($messages === []) {
            return null;
        }

        return implode(' | ', array_slice(array_unique($messages), 0, 5));
    }

    private static function sanitizeFailureSummary(string $summary): string
    {
        if ($summary === '') {
            return '';
        }

        $summary = preg_replace('/\S+@\S+\.\S+/', '[redacted]', $summary) ?? $summary;

        if (strlen($summary) > self::FAILURE_SUMMARY_MAX_LENGTH) {
            return substr($summary, 0, self::FAILURE_SUMMARY_MAX_LENGTH) . '...';
        }

        return $summary;
    }

    private static function safeSetSpanOk($span): void
    {
        if ($span === null) {
            return;
        }

        try {
            $span->setStatus(StatusCode::STATUS_OK);
        } catch (Throwable) {
        }
    }

    private static function safeEndSpan($span, ?ScopeInterface $scope): void
    {
        try {
            if ($scope !== null) {
                $scope->detach();
            }
        } catch (Throwable) {
        }

        try {
            if ($span !== null) {
                $span->end();
            }
        } catch (Throwable) {
        }

        try {
            Globals::tracerProvider()->forceFlush();
        } catch (Throwable) {
        }
    }
}
