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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model;

use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;

/**
 * @deprecated since version 21.7.0, to be removed in 22.0. Use \oat\tao\model\taskQueue\TaskLogActionTrait instead
 */
trait TaskQueueActionTrait
{

    /**
     * @var Task[]
     */
    protected $tasks = [];

    /**
     * Template method to generate task data to be returned to the end user.
     * @param string $taskId
     * @return array
     */
    protected function getTaskData($taskId)
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
        if (!isset($this->tasks[$taskId])) {
            /** @var Queue $taskQueue */
            $taskQueue = $this->getServiceManager()->get(Queue::SERVICE_ID);
            $task = $taskQueue->getTask($taskId);
            if ($task === null) {
                throw new \common_exception_NotFound(__('Task not found'));
            }
            $this->tasks[$taskId] = $task;
        }
        return $this->tasks[$taskId];
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

    /**
     * @param $report
     * @return array
     */
    protected function getPlainReport(\common_report_Report $report)
    {
        $result = [];
        $result[] = $report;
        if ($report->hasChildren()) {
            foreach ($report as $r) {
                $result = array_merge($result, $this->getPlainReport($r));
            }
        }
        return $result;
    }
}