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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools;

use common_report_Report;
use InvalidArgumentException;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\task\migration\AbstractStatementMigrationTask;
use oat\tao\model\task\migration\service\PositionTracker;
use oat\tao\model\taskQueue\Queue;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\TaskLogActionTrait;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\taoTaskQueue\model\QueueBroker\RdsQueueBroker;
use oat\taoTaskQueue\scripts\tools\InitializeQueue;
use oat\taoTaskQueue\scripts\tools\RunWorker;
use RuntimeException;
use Throwable;

class StatementsMigrationWrapper extends ScriptAction
{
    use OntologyAwareTrait;
    use TaskLogActionTrait;

    protected function provideOptions()
    {
        return [
            'chunkSize' => [
                'prefix' => 'c',
                'longPrefix' => 'chunkSize',
                'required' => false,
                'cast' => 'integer',
                'defaultValue' => 10000,
                'description' => 'Amount of `statements` rows provided into taskqueue to proceeded with'
            ],
            'pickSize' => [
                'prefix' => 'p',
                'longPrefix' => 'pickSize',
                'required' => false,
                'cast' => 'integer',
                'defaultValue' => 0,
                'description' => 'Amount of items proceed in chunk (for test purposes)'
            ],
            'recoveryMode' => [
                'prefix' => 'r',
                'longPrefix' => 'recoveryMode',
                'required' => false,
                'cast' => 'boolean',
                'defaultValue' => false,
                'description' => 'Starts recovery by resuming from the last chunk'
            ],
            'repeat' => [
                'prefix' => 'rp',
                'longPrefix' => 'repeat',
                'required' => false,
                'cast' => 'boolean',
                'defaultValue' => true,
                'description' => 'Scan all the records to the very end'
            ],
            'start' => [
                'prefix' => 's',
                'longPrefix' => 'start',
                'required' => false,
                'cast' => 'integer',
                'defaultValue' => 1,
                'description' => 'Sliding window start range'
            ],
            'queue' => [
                'prefix' => 'q',
                'longPrefix' => 'queue',
                'required' => false,
                'cast' => 'string',
                'description' => 'Define task queue broker name to work at'
            ],

            'target' => [
                'prefix' => 't',
                'longPrefix' => 'target',
                'required' => true,
                'cast' => 'string',
                'description' => sprintf(
                    'Define unit processing task (must extend "%s")',
                    AbstractStatementMigrationTask::class
                )
            ],
        ];
    }

    protected function provideDescription()
    {
    }

    protected function run()
    {
        $startedAt = time();

        $chunkSize = $this->getOption('chunkSize');
        $start = $this->getOption('start');
        $isRecovery = $this->getOption('recoveryMode');
        $pickSize = $this->getOption('pickSize');
        $repeat = $this->getOption('repeat');
        $queue = $this->getOption('queue');

        $taskClass = $this->detectTargetClass($this->getOption('target'));

        if ($queue) {
            $report = $this->configureQueues($queue, $taskClass);
            $result = common_report_Report::createSuccess(
                sprintf(
                    '1. Please restart fpm to apply changes ' . PHP_EOL .
                    '2. Execute `php index.php "%s" --queue=%s`' . PHP_EOL .
                    '3. Re-run original command bypassing `queue` parameter' . PHP_EOL,
                    RunWorker::class,
                    $queue
                )
            );
            $result->add($report);
            return $result;
        }

        if ($isRecovery) {
            $start = $this->getServiceLocator()->get(PositionTracker::class)->getLastPosition($taskClass, $start);
        }

        try {
            $task = $this->spawnTask($start, $chunkSize, $pickSize, $taskClass, $repeat);

            $taskLogEntity = $this->getTaskLogEntity($task->getId());
            $taskReport = $taskLogEntity->getReport();

            if (0 === strcasecmp($taskLogEntity->getStatus()->getLabel(), TaskLogInterface::STATUS_FAILED)) {
                throw new RuntimeException('task failed please refer logs');
            }
        } catch (Throwable $e) {
            return common_report_Report::createFailure($e->getMessage());
        }

        $report = common_report_Report::createSuccess(
            sprintf(
                "Operation took %fsec and %dMb",
                (time() - $startedAt),
                memory_get_peak_usage(true) / 1024 / 1024
            )
        );

        if (null !== $taskReport) {
            $report->add($taskReport);
        }

        return $report;
    }

    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints the help.'
        ];
    }

    protected function returnJson($data, $httpStatus = 200)
    {
    }

    private function spawnTask(
        int $start,
        int $chunkSize,
        int $pickSize,
        string $taskClass,
        bool $repeat = true
    ): CallbackTaskInterface {
        /** @var QueueDispatcherInterface $queueDispatcher */
        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
        return $queueDispatcher->createTask(
            new $taskClass(),
            [$start, $chunkSize, $pickSize, $repeat],
            sprintf(
                'Unit processing by %s started from %s with chunk size %s',
                $taskClass,
                $start,
                $chunkSize
            )
        );
    }

    private function configureQueues(string $queue, string $targetClass): common_report_Report
    {
        /** @var QueueDispatcher $queueService */
        $queueService = $this->getServiceManager()->get(QueueDispatcher::SERVICE_ID);
        $existingQueues = $queueService->getOption(QueueDispatcherInterface::OPTION_QUEUES);
        $newQueue = new Queue($queue, new RdsQueueBroker('default', 1), 30);
        $existingOptions = $queueService->getOptions();
        $existingOptions[QueueDispatcherInterface::OPTION_QUEUES] = array_unique(
            array_merge($existingQueues, [$newQueue])
        );
        $existingAssociations = $queueService->getOption(QueueDispatcherInterface::OPTION_TASK_TO_QUEUE_ASSOCIATIONS);
        $existingOptions[QueueDispatcherInterface::OPTION_TASK_TO_QUEUE_ASSOCIATIONS] = array_merge(
            $existingAssociations,
            [$targetClass => $queue]
        );

        $queueService->setOptions($existingOptions);
        $this->getServiceManager()->register(QueueDispatcherInterface::SERVICE_ID, $queueService);
        $initializer = new InitializeQueue();
        $this->propagate($initializer);
        return $initializer([]);
    }


    private function detectTargetClass(string $target): string
    {
        if (class_exists($target) && is_a($target, AbstractStatementMigrationTask::class, true)) {
            return $target;
        }
        throw new InvalidArgumentException(
            sprintf('Task must extend %s', AbstractStatementMigrationTask::class)
        );
    }
}
