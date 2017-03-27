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
use oat\oatbox\task\TaskPayload;
use oat\oatbox\task\Task;
use oat\oatbox\filesystem\FileSystemService;

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

        $taskQueue = $this->getServiceManager()->get(Queue::SERVICE_ID);

        if($taskQueue instanceof TaskPayload) {
            $dataPayLoad =  $taskQueue->getPayload($user->getIdentifier());

            $this->returnJson($dataPayLoad);
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
            $this->returnJson([
                'success' => true,
                'data' => $data,
            ]);
            return;
        }
        $this->returnJson([
            'success' => false,
        ]);
        return;
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
            $this->returnError(__('unkown task id %s' , $taskId));
            return;
        }
        if(empty($task)) {
            $this->returnError(__('unkown task id %s' , $taskId));
            return;
        }
        try {
            $taskService->updateTaskStatus($taskId , Task::STATUS_ARCHIVED);
            $task   = $taskService->getTask($taskId);;
            $this->returnJson([
                'success' => true ,
                'data'=>[
                    'id' => $taskId,
                    'status' => $task->getStatus()
                ]
            ]);
            return;
        } catch (\Exception $e) {
            $this->returnError(__('impossible to update task status'));
            return;
        }
    }

    public function download(){
        if($this->hasRequestParameter('taskId')){
            /**
             * @var $task \oat\Taskqueue\JsonTask
             */
            $task   = $this->getTask($this->getRequestParameter('taskId'));
            $report = $task->getReport();
            $report = \common_report_Report::jsonUnserialize($report);

            if(!is_null($report)){
                $filename = '';
                /** @var \common_report_Report $success */
                foreach ($report->getSuccesses() as $success){
                    if(!is_null($filename = $success->getData())){
                        break;
                    }
                }
                /** @var FileSystemService $fileSystem */
                $fileSystem = $this->getServiceManager()->get(FileSystemService::SERVICE_ID);
                $directory = $fileSystem->getDirectory('taskQueueStorage');
                $file = $directory->getFile($filename);
                tao_helpers_Http::returnStream($file->readPsrStream());
                return;
            }
        }
        $this->returnJson([
            'success' => false,
        ]);
        return;
    }
}
