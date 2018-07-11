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

namespace oat\tao\model\taskQueue\Queue\Broker;

use oat\tao\model\taskQueue\Task\TaskInterface;

/**
 * Stores tasks in memory. It accomplishes Sync Queue mechanism.
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class InMemoryQueueBroker extends AbstractQueueBroker implements SyncQueueBrokerInterface
{
    /**
     * @var \SplQueue
     */
    private $queue;

    /**
     * @return \SplQueue
     */
    private function getQueue()
    {
        if (is_null($this->queue)) {
            $this->createQueue();
        }
        return $this->queue;
    }

    /**
     * Initiates the SplQueue
     */
    public function createQueue()
    {
        $this->queue = new \SplQueue();
        $this->logDebug('Memory Queue created');
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->getQueue()->count();
    }

    /**
     * @param TaskInterface $task
     * @return bool
     */
    public function push(TaskInterface $task)
    {
        $this->getQueue()->enqueue($this->serializeTask($task));

        return true;
    }

    /**
     * Overwriting the parent totally because in this case we need a much simpler logic for popping messages.
     *
     * @return mixed|null
     */
    public function pop()
    {
        if (!$this->count()) {
            return null;
        }

        $task = $this->getQueue()->dequeue();

        return $this->unserializeTask($task, '');
    }

    /**
     * Do nothing.
     */
    protected function doPop()
    {}

    /**
     * Do nothing, because dequeue automatically deletes the message from the queue
     *
     * @param TaskInterface $task
     */
    public function delete(TaskInterface $task)
    {}

    /**
     * Do nothing.
     *
     * @param string $receipt
     * @param array  $logContext
     */
    protected function doDelete($receipt, array $logContext = [])
    {}
}