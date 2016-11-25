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
 */

namespace oat\tao\controller\api;

use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;

/**
 * Rest API controller for task queue
 *
 * Class TaskQueue
 * @package oat\tao\controller\api
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class TaskQueue extends \tao_actions_RestController
{
    const TASK_ID_PARAM = 'id';

    /**
     * Get task data by identifier
     */
    public function get()
    {
        try {
            if (!$this->hasRequestParameter(self::TASK_ID_PARAM)) {
                throw new \common_exception_MissingParameter(self::TASK_ID_PARAM, $this->getRequestURI());
            }
            $data = $this->getTaskData($this->getRequestParameter(self::TASK_ID_PARAM));
            $this->returnSuccess($data);
        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * Template method to generate task data to be returned to the end user.
     * @param string $taskId
     * @return array
     */
    private function getTaskData($taskId)
    {
        $task             = $this->getTask($taskId);
        $result['id']     = $this->getTaskId($task);
        $result['status'] = $this->getTaskStatus($task);
        $result['report'] = $this->getTaskReport($task);

        return $result;
    }

    /**
     * Get task instance from queue by identifier
     * @param $taskId task identifier
     * @throws \common_exception_NotFound
     * @return Task
     */
    protected function getTask($taskId)
    {
        /** @var Queue $taskQueue */
        $taskQueue = $this->getServiceManager()->get(Queue::CONFIG_ID);
        $task = $taskQueue->getTask($taskId);
        if ($task === null) {
            throw new \common_exception_NotFound(__('Task not found'));
        }
        return $task;
    }

    /**
     * Return task report. Method may be overridden to comply special format of report
     * @param Task $task
     * @return null
     */
    protected function getTaskReport(Task $task)
    {
        return $task->getReport();
    }

    /**
     * Return task status
     * @param Task $task
     * @return null
     */
    protected function getTaskStatus(Task $task)
    {
        return $task->getStatus();
    }

    /**
     * Return task identifier
     * @param Task $task
     * @return null
     */
    protected function getTaskId(Task $task)
    {
        return $task->getId();
    }
}
