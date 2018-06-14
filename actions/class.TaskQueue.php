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

use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\TaskQueueActionTrait;

/**
 * Rest API controller for task queue
 *
 * @package oat\tao\controller\api
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class tao_actions_TaskQueue extends \tao_actions_RestController
{
    use TaskQueueActionTrait;

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

            $taskId = $this->getRequestParameter(self::TASK_ID_PARAM);

            /** @var TaskLogInterface $taskLog */
            $taskLog = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);

            $filter = (new TaskLogFilter())
                ->eq(TaskLogBrokerInterface::COLUMN_ID, $taskId);

            // trying to get data from the new task queue first
            $collection = $taskLog->search($filter);

            if ($collection->isEmpty()) {
                // if we don't have the task in the new queue,
                // loading the data from the old one
                $data = $this->getTaskData($taskId);
            } else {
                // we have the task in the new queue
                $entity = $collection->first();
                $status = (string) $entity->getStatus();

                if ($entity->getStatus()->isInProgress()) {
                    $status = \oat\oatbox\task\Task::STATUS_RUNNING;
                } elseif ($entity->getStatus()->isCompleted() || $entity->getStatus()->isFailed()) {
                    $status = \oat\oatbox\task\Task::STATUS_FINISHED;
                }

                $data['id'] = $entity->getId();
                $data['status'] = $status;
                //convert to array for xml response.
                $data['report'] = json_decode(json_encode($entity->getReport()));
            }

            $this->returnSuccess($data);
        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * Returns only the status of a task, independently from the owner.
     *
     * NOTE: only works for tasks handled by the new task queue.
     */
    public function getStatus()
    {
        try {
            if (!$this->hasRequestParameter(self::TASK_ID_PARAM)) {
                throw new \common_exception_MissingParameter(self::TASK_ID_PARAM, $this->getRequestURI());
            }

            /** @var TaskLogInterface $taskLogService */
            $taskLogService = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);

            $entity = $taskLogService->getById((string) $this->getRequestParameter(self::TASK_ID_PARAM));

            $this->returnSuccess((string) $entity->getStatus());
        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }
}
