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

namespace oat\tao\model\taskQueue\Worker;

use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLogInterface;

final class OneTimeTask extends AbstractWorker
{
    /** @var TaskInterface */
    private $task;

    /**
     * Start processing tasks from a given queue
     *
     * @return string
     */
    public function run()
    {
        $this->logDebug('Starting Task.');
        try{
            return $this->processTask($this->task);

        } catch (\Exception $e) {
            $this->logError('Error processing task '. $e->getMessage());
        }

        $this->logDebug('Task finished.');

        return TaskLogInterface::STATUS_FAILED;
    }

    /**
     * @param TaskInterface $task
     */
    public function setTask($task)
    {
        $this->task = $task;
    }
}