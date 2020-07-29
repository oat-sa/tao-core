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
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\TaskLogActionTrait;
use oat\tao\model\taskQueue\TaskLogInterface;
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

        $taskClass = $this->detectTargetClass($this->getOption('target'));

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
                'Operation took %fsec and %dMb',
                time() - $startedAt,
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
            'description' => 'Prints the help.',
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
    ): CallbackTaskInterface
    {
        /** @var QueueDispatcherInterface $queueDispatcher */
        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
        return $queueDispatcher->createTask(
            new $taskClass(),
            [
                'start' => $start,
                'chunkSize' => $chunkSize,
                'pickSize' => $pickSize,
                'repeat' => $repeat,
            ],
            sprintf(
                'Unit processing by %s started from %s with chunk size %s',
                $taskClass,
                $start,
                $chunkSize
            )
        );
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
