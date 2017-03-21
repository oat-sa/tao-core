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

use oat\tao\model\TaskQueueActionTrait;
use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;

/**
 * Rest API controller for task queue
 *
 * Class tao_actions_TaskQueue
 * @package oat\tao\controller\api
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class tao_actions_TaskQueueData extends \tao_actions_CommonModule
{

    use TaskQueueActionTrait;

    public function getTasks()
    {
        $user = common_session_SessionManager::getSession()->getUser();

        /**
         * @var $taskQueue oat\Taskqueue\Persistence\RdsQueue
         */
        $taskQueue = $this->getServiceManager()->get(Queue::SERVICE_ID);

        if(is_a($taskQueue , 'oat\Taskqueue\Persistence\RdsQueue', true)) {
            /** @var oat\Taskqueue\Action\TaskQueueSearch $dataPayLoad */
            $dataPayLoad =  $taskQueue->getPayload($user->getIdentifier());

            echo json_encode($dataPayLoad);
            return;
        }
    }

    public function getStatus(){
        if($this->hasRequestParameter('taskId')){
            /**
             * @var $task \oat\Taskqueue\JsonTask
             */
            $task   = $this->getTask($this->getRequestParameter('taskId'));
            $report = $task->getReport();
            $data = [
                'status'  => $task->getStatus(),
                'label'  => $task->getLabel(),
                'creationDate'  => strtotime($task->getCreationDate()),
                'report' => $report
            ];
            return $this->returnJson([
                'success' => true,
                'data' => $data,
            ]);
        }
        return $this->returnJson([
            'success' => false,
        ]);
    }

    public function archiveTask() {
        $taskId      = $this->getRequestParameter('taskId');
        /**
         * @var $taskService Queue
         */
        $taskService = $this->getServiceManager()->get(Queue::SERVICE_ID);
        try {
            $task        = $this->getTask($taskId);

        } catch (\Exception $e) {
            return $this->returnError(__('unkown task id %s' , $taskId));
        }
        if(empty($task)) {
            return $this->returnError(__('unkown task id %s' , $taskId));
        }
        try {
            $taskService->updateTaskStatus($taskId , Task::STATUS_ARCHIVED);
            $task   = $taskService->getTask($taskId);;
            return $this->returnJson([
                'success' => true ,
                'data'=>[
                    'id' => $taskId,
                    'status' => $task->getStatus()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->returnError(__('impossible to update task status'));
        }
    }
}
