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

namespace oat\tao\model\taskQueue;

use oat\tao\model\taskQueue\Queue\TaskSelector\SelectorStrategyInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
interface QueueDispatcherInterface extends QueuerInterface, LoggerAwareInterface
{
    const SERVICE_ID = 'tao/taskQueue';

    const FILE_SYSTEM_ID = 'taskQueueStorage';

    /**
     * Array of queues
     */
    const OPTION_QUEUES = 'queues';

    /**
     * Name of the default queue. Task without specified queue will be published here.
     */
    const OPTION_DEFAULT_QUEUE = 'default_queue';

    /**
     * An array of tasks names with the specified queue where the tasks needs to be published to.
     */
    const OPTION_TASK_TO_QUEUE_ASSOCIATIONS = 'task_to_queue_associations';

    const OPTION_TASK_LOG = 'task_log';

    const OPTION_TASK_SELECTOR_STRATEGY = 'task_selector_strategy';

    const QUEUE_PREFIX = 'TQ';

    /**
     * Add new Queue.
     *
     * @param QueueInterface $queue
     * @return QueueDispatcherInterface
     */
    public function addQueue(QueueInterface $queue);

    /**
     * @param QueueInterface[] $queues
     * @return QueueDispatcherInterface
     */
    public function setQueues(array $queues);

    /**
     * @param string $queueName
     * @return QueueInterface
     */
    public function getQueue($queueName);

    /**
     * @return QueueInterface[]
     */
    public function getQueues();

    /**
     * Get the names of the registered queues.
     *
     * @return array
     */
    public function getQueueNames();

    /**
     * Has the given queue/queue name already been set?
     *
     * @param string $queueName
     * @return bool
     */
    public function hasQueue($queueName);

    /**
     * Get the default queue.
     *
     * @return QueueInterface
     */
    public function getDefaultQueue();

    /**
     * Link a task to a queue.
     *
     * @param string|object $taskName
     * @param string $queueName
     * @return QueueDispatcherInterface
     */
    public function linkTaskToQueue($taskName, $queueName);

    /**
     * Get the linked tasks.
     *
     * @return array
     */
    public function getLinkedTasks();

    /**
     * Initialize queues.
     *
     * @return void
     */
    public function initialize();

    /**
     * Create a task to be managed by the queue from any callable
     *
     * @param callable    $callable
     * @param array       $parameters
     * @param null|string $label Label for the task
     * @param null|TaskInterface $parent
     * @param boolean $masterStatus
     * @return CallbackTaskInterface
     */
    public function createTask(callable $callable, array $parameters = [], $label = null, TaskInterface $parent = null, $masterStatus = false);

    /**
     * Are all queues a sync one?
     *
     * @return bool
     */
    public function isSync();

    /**
     * @param SelectorStrategyInterface $selectorStrategy
     * @return QueueDispatcherInterface
     */
    public function setTaskSelector(SelectorStrategyInterface $selectorStrategy);

    /**
     * Seconds for the worker to wait if there is no task.
     *
     * @return int
     */
    public function getWaitTime();
}