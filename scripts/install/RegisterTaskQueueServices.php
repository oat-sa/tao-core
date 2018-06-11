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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\tao\model\taskQueue\Queue;
use oat\tao\model\taskQueue\Queue\Broker\InMemoryQueueBroker;
use oat\tao\model\taskQueue\Queue\TaskSelector\WeightStrategy;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\TaskLog;
use oat\tao\model\taskQueue\TaskLogInterface;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class RegisterTaskQueueServices extends InstallAction
{
    public function __invoke($params)
    {
        $taskLogService = new TaskLog([
            TaskLogInterface::OPTION_TASK_LOG_BROKER => new TaskLog\Broker\RdsTaskLogBroker('default')
        ]);
        $this->registerService(TaskLogInterface::SERVICE_ID, $taskLogService);

        try {
            $taskLogService->createContainer();
        } catch (\Exception $e) {
            return \common_report_Report::createFailure('Creating task log container failed');
        }

        $queueService = new QueueDispatcher([
            QueueDispatcherInterface::OPTION_QUEUES       => [
                new Queue('queue', new InMemoryQueueBroker())
            ],
            QueueDispatcherInterface::OPTION_TASK_LOG     => TaskLogInterface::SERVICE_ID,
            QueueDispatcherInterface::OPTION_TASK_TO_QUEUE_ASSOCIATIONS => [],
            QueueDispatcherInterface::OPTION_TASK_SELECTOR_STRATEGY => new WeightStrategy()
        ]);

        $this->registerService(QueueDispatcherInterface::SERVICE_ID, $queueService);

        return \common_report_Report::createSuccess('Task Queue service successfully registered.');
    }
}