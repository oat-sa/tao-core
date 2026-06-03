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

use oat\oatbox\log\LoggerService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\taskQueue\QueueInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Sdk;
use Throwable;

/**
 * Propagates W3C trace context through task queue metadata and creates enqueue/process spans.
 */
final class TaskQueueTelemetry
{
    public const METADATA_TRACE_CONTEXT_KEY = '__otel_trace_context__';

    private const TRACER_NAME = 'tao-task-queue';

    private const ERROR_STATUS_ENQUEUE = 'task enqueue error';

    private const ERROR_STATUS_PROCESS = 'task processing error';

    private const EXCEPTION_MESSAGE_MAX_LENGTH = 500;

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

        $taskClass = self::resolveTaskClassName($task);
        $queueName = $queue->getName();
        $tracer = Globals::tracerProvider()->getTracer(self::TRACER_NAME);

        $span = $tracer
            ->spanBuilder(sprintf('ENQUEUE %s', $taskClass))
            ->setSpanKind(SpanKind::KIND_PRODUCER)
            ->setAttribute('messaging.system', 'tao_task_queue')
            ->setAttribute('messaging.operation', 'publish')
            ->setAttribute('messaging.destination.name', $queueName)
            ->setAttribute('task.class', $taskClass)
            ->setAttribute('task.id', $task->getId())
            ->startSpan();

        $scope = $span->activate();

        try {
            self::injectTraceContextIntoTask($task);

            $result = $enqueue();

            $span->setStatus(StatusCode::STATUS_OK);

            return $result;
        } catch (Throwable $exception) {
            self::markSpanFailed($span, $exception, 'enqueue');

            throw $exception;
        } finally {
            $span->end();
            $scope->detach();
            self::forceFlush();
        }
    }

    /**
     * @param callable(): string $processor
     */
    public static function traceProcessTask(TaskInterface $task, callable $processor): string
    {
        if (!self::isEnabled()) {
            return $processor();
        }

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
        $scope = $span->activate();

        try {
            $status = $processor();

            $span->setAttribute('task.status', (string) $status);
            $span->setStatus(StatusCode::STATUS_OK);

            return $status;
        } catch (Throwable $exception) {
            self::markSpanFailed($span, $exception, 'process');

            throw $exception;
        } finally {
            $span->end();
            $scope->detach();
            self::forceFlush();
        }
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

    private static function injectTraceContextIntoTask(TaskInterface $task): void
    {
        $carrier = [];
        TraceContextPropagator::getInstance()->inject($carrier);

        if ($carrier === []) {
            return;
        }

        $task->setMetadata(self::METADATA_TRACE_CONTEXT_KEY, $carrier);
    }

    private static function extractContextFromTask(TaskInterface $task): Context
    {
        $carrier = $task->getMetadata(self::METADATA_TRACE_CONTEXT_KEY, []);

        if (!is_array($carrier) || $carrier === []) {
            return Context::getCurrent();
        }

        return TraceContextPropagator::getInstance()->extract($carrier);
    }

    private static function markSpanFailed($span, Throwable $exception, string $operation): void
    {
        $span->recordException($exception);
        $span->setStatus(
            StatusCode::STATUS_ERROR,
            $operation === 'enqueue' ? self::ERROR_STATUS_ENQUEUE : self::ERROR_STATUS_PROCESS
        );
        self::logException($exception, $operation);
    }

    private static function logException(Throwable $exception, string $operation): void
    {
        try {
            $logger = ServiceManager::getServiceManager()->get(LoggerService::SERVICE_ID);
            $logger->error(
                sprintf(
                    'Task queue telemetry %s failed [%s]: %s',
                    $operation,
                    get_class($exception),
                    self::sanitizeExceptionMessage($exception)
                ),
                [
                    'exception_class' => get_class($exception),
                    'exception_code' => $exception->getCode(),
                ]
            );
        } catch (Throwable) {
            // Logging must not break task processing.
        }
    }

    private static function sanitizeExceptionMessage(Throwable $exception): string
    {
        $message = $exception->getMessage();

        if ($message === '') {
            return '';
        }

        $message = preg_replace('/\S+@\S+\.\S+/', '[redacted]', $message) ?? $message;

        if (strlen($message) > self::EXCEPTION_MESSAGE_MAX_LENGTH) {
            return substr($message, 0, self::EXCEPTION_MESSAGE_MAX_LENGTH) . '...';
        }

        return $message;
    }

    private static function forceFlush(): void
    {
        try {
            Globals::tracerProvider()->forceFlush();
        } catch (Throwable) {
            // Export must not break task processing.
        }
    }
}
