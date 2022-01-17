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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\taskQueue\TaskAggregation;

use oat\oatbox\reporting\Report;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLogInterface;
use Psr\Log\LoggerInterface;

class TaskAggregationService
{
    /** @var array<string, TaskInterface> */
    private $taskForAggregationById = [];
    /** @var QueueDispatcherInterface */
    private $queueDispatcher;
    /** @var TaskLogInterface */
    private $taskLog;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        QueueDispatcherInterface $queueDispatcher,
        TaskLogInterface         $taskLog,
        LoggerInterface $logger
    ) {
        $this->queueDispatcher = $queueDispatcher;
        $this->taskLog = $taskLog;
        $this->logger = $logger;
    }

    public function extractTaskParamsForAggregation(string $queueName, int $limitForAggregation = 10): array
    {
        $paramsForAggregation = [];
        $queue = $this->queueDispatcher->getQueue($queueName);
        while (
            count($paramsForAggregation) < $limitForAggregation &&
            ($task = $queue->dequeue()) !== null
        ) {
            $rowsTouched = $this->taskLog->setStatus(
                $task->getId(),
                TaskLogInterface::STATUS_RUNNING,
                TaskLogInterface::STATUS_DEQUEUED
            );
            // if the task is being executed by another worker, just skip it
            if (!$rowsTouched) {
                continue;
            }
            $this->taskForAggregationById[$task->getId()] = $task;
            $paramsForAggregation[$task->getId()] = $task->getParameters();
            $this->logger->info(sprintf('Task %s is aggregated', $task->getId()));
        }

        return $paramsForAggregation;
    }

    public function ackSuccess(string $queueName, string $taskId): void
    {
        if (array_key_exists($taskId, $this->taskForAggregationById)) {
            $this->taskLog->setReport(
                $taskId,
                Report::createSuccess('Aggregated task successfully handled'),
                TaskLogInterface::STATUS_COMPLETED
            );
            $queue = $this->queueDispatcher->getQueue($queueName);
            $queue->acknowledge($this->taskForAggregationById[$taskId]);
        }
    }

    public function ackFailure(string $queueName, string $taskId, string $failureMessage): void
    {
        if (array_key_exists($taskId, $this->taskForAggregationById)) {
            $this->taskLog->setReport(
                $taskId,
                Report::createSuccess('Aggregated task handling failed: ' . $failureMessage),
                TaskLogInterface::STATUS_COMPLETED
            );
            $queue = $this->queueDispatcher->getQueue($queueName);
            $queue->acknowledge($this->taskForAggregationById[$taskId]);
        }
    }
}
