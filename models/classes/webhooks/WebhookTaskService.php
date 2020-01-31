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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\webhooks;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\webhooks\task\WebhookTask;
use oat\tao\model\webhooks\task\WebhookTaskParams;

class WebhookTaskService extends ConfigurableService implements WebhookTaskServiceInterface
{
    /**
     * Should be called in updater/install script for specific env to
     * link webhook tasks to specific queue which is already registered in queue dispatcher
     * @param string $queueName
     */
    public function linkTaskToQueue($queueName)
    {
        $this->getQueueDispatcher()->linkTaskToQueue(WebhookTask::class, $queueName);
    }

    /**
     * Create and enqueue task for performing webhook
     * @param WebhookTaskParams $webhookTaskParams
     */
    public function createTask(WebhookTaskParams $webhookTaskParams)
    {
        $task = new WebhookTask();
        $this->propagate($task);

        /** @var QueueDispatcherInterface $queueDispatcher */
        $this->getQueueDispatcher()->createTask($task, (array) $webhookTaskParams, 'Event Webhook');
    }

    /**
     * @return QueueDispatcherInterface
     */
    private function getQueueDispatcher()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
    }
}
