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

use oat\tao\model\taskQueue\QueueInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanInterface;
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

    private const LOG_PREFIX = '[tao-task-queue-otel]';

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

        self::logTraceDiagnostics('enqueue/before_span', [
            'queue' => $queueName,
            'task_class' => $taskClass,
            'task_id' => $task->getId(),
            'label' => 'active_parent_before_enqueue_span',
        ]);

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

        self::logTraceDiagnostics('enqueue/span_active', [
            'queue' => $queueName,
            'task_class' => $taskClass,
            'task_id' => $task->getId(),
            'enqueue_span' => self::describeSpan($span),
            'label' => 'enqueue_span_after_activate',
        ]);

        try {
            $injectedCarrier = self::injectTraceContextIntoTask($task);

            self::logTraceDiagnostics('enqueue/after_inject', [
                'queue' => $queueName,
                'task_class' => $taskClass,
                'task_id' => $task->getId(),
                'injected_carrier' => $injectedCarrier,
                'stored_metadata' => $task->getMetadata(self::METADATA_TRACE_CONTEXT_KEY, null),
            ]);

            $result = $enqueue();

            $span->setStatus(StatusCode::STATUS_OK);

            return $result;
        } catch (Throwable $exception) {
            $span->recordException($exception);
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());

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
        $storedCarrier = $task->getMetadata(self::METADATA_TRACE_CONTEXT_KEY, null);
        $hasStoredCarrier = is_array($storedCarrier) && $storedCarrier !== [];
        $parentContext = self::extractContextFromTask($task);

        self::logTraceDiagnostics('process/before_span', [
            'task_class' => $taskClass,
            'task_id' => $task->getId(),
            'has_stored_trace_context' => $hasStoredCarrier,
            'stored_carrier' => is_array($storedCarrier) ? $storedCarrier : null,
            'stored_traceparent' => self::parseTraceparent(is_array($storedCarrier) ? $storedCarrier : null),
            'label' => 'active_parent_before_process_span',
        ]);

        $spanBuilder = $tracer
            ->spanBuilder(sprintf('PROCESS %s', $taskClass))
            ->setSpanKind(SpanKind::KIND_CONSUMER)
            ->setAttribute('messaging.system', 'tao_task_queue')
            ->setAttribute('messaging.operation', 'process')
            ->setAttribute('task.class', $taskClass)
            ->setAttribute('task.id', $task->getId());

        if ($hasStoredCarrier) {
            $spanBuilder = $spanBuilder->setParent($parentContext);
        }

        self::logTraceDiagnostics('process/parent_context', [
            'task_class' => $taskClass,
            'task_id' => $task->getId(),
            'will_set_parent_from_metadata' => $hasStoredCarrier,
        ]);

        $span = $spanBuilder->startSpan();
        $scope = $span->activate();

        self::logTraceDiagnostics('process/span_active', [
            'task_class' => $taskClass,
            'task_id' => $task->getId(),
            'process_span' => self::describeSpan($span),
            'label' => 'process_span_after_activate',
        ]);

        try {
            $status = $processor();

            $span->setAttribute('task.status', (string) $status);
            $span->setStatus(StatusCode::STATUS_OK);

            return $status;
        } catch (Throwable $exception) {
            $span->recordException($exception);
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());

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

    /**
     * @return array<string, string>|null
     */
    private static function injectTraceContextIntoTask(TaskInterface $task): ?array
    {
        $carrier = [];
        TraceContextPropagator::getInstance()->inject($carrier);

        if ($carrier === []) {
            return null;
        }

        $task->setMetadata(self::METADATA_TRACE_CONTEXT_KEY, $carrier);

        return $carrier;
    }

    private static function extractContextFromTask(TaskInterface $task): Context
    {
        $carrier = $task->getMetadata(self::METADATA_TRACE_CONTEXT_KEY, []);

        if (!is_array($carrier) || $carrier === []) {
            return Context::getCurrent();
        }

        return TraceContextPropagator::getInstance()->extract($carrier);
    }

    private static function forceFlush(): void
    {
        try {
            Globals::tracerProvider()->forceFlush();
        } catch (Throwable) {
            // Export must not break task processing.
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private static function logTraceDiagnostics(string $event, array $context = []): void
    {
        try {
            $payload = array_merge(
                [
                    'event' => $event,
                    'sapi' => PHP_SAPI,
                    'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
                    'otel_service_name' => getenv('OTEL_SERVICE_NAME') ?: null,
                    'active_span' => self::describeSpan(Span::getCurrent()),
                ],
                $context,
            );

            error_log(self::LOG_PREFIX . ' ' . json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        } catch (Throwable) {
            // Diagnostics must not break task processing.
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function describeSpan(SpanInterface $span): array
    {
        $context = $span->getContext();

        return [
            'valid' => $context->isValid(),
            'sampled' => $context->isValid() ? $context->isSampled() : null,
            'recording' => $span->isRecording(),
            'remote' => $context->isValid() ? $context->isRemote() : null,
            'trace_id' => $context->isValid() ? $context->getTraceId() : null,
            'span_id' => $context->isValid() ? $context->getSpanId() : null,
        ];
    }

    /**
     * @param array<string, string>|null $carrier
     *
     * @return array<string, string|null>|null
     */
    private static function parseTraceparent(?array $carrier): ?array
    {
        if ($carrier === null) {
            return null;
        }

        $traceparent = $carrier['traceparent'] ?? $carrier['Traceparent'] ?? null;
        if (!is_string($traceparent) || $traceparent === '') {
            return ['raw' => null];
        }

        $parts = explode('-', $traceparent);
        if (count($parts) < 4) {
            return ['raw' => $traceparent, 'parse_error' => 'unexpected_format'];
        }

        return [
            'raw' => $traceparent,
            'version' => $parts[0],
            'trace_id' => $parts[1],
            'parent_span_id' => $parts[2],
            'flags' => $parts[3],
        ];
    }
}
