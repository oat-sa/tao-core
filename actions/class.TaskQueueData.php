<?php

declare(strict_types=1);

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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */


use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;
use oat\tao\model\TaskQueueActionTrait;

/**
 * Rest API controller for task queue
 *
 * @deprecated since version 21.7.0, to be removed in 22.0. Use \tao_actions_TaskQueueWebApi instead.
 *
 * @author GARCIA Christophe <christophe.garcia@taotesting.com>
 */
class tao_actions_TaskQueueData extends \tao_actions_CommonModule
{
    use TaskQueueActionTrait;

    public function getTasks(): void
    {
        $user = $this->getSession()->getUser();

        $taskQueue = $this->getServiceLocator()->get(Queue::SERVICE_ID);

        $dataPayLoad = $taskQueue->getPayload($user->getIdentifier());

        $this->returnJson($dataPayLoad);
        return;
    }

    public function getStatus(): void
    {
        if ($this->hasRequestParameter('taskId')) {
            /** @var \oat\Taskqueue\JsonTask */
            $task = $this->getTask($this->getRequestParameter('taskId'));
            $report = $task->getReport();
            $data = [
                'status' => $task->getStatus(),
                'label' => $task->getLabel(),
                'creationDate' => $task->getCreationDate(),
                'report' => $report,
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

    public function archiveTask(): void
    {
        $taskId = $this->getRequestParameter('taskId');
        /** @var Queue */
        $taskService = $this->getServiceLocator()->get(Queue::SERVICE_ID);
        try {
            $task = $this->getTask($taskId);
        } catch (\Throwable $e) {
            $this->returnError(__('unkown task id %s', $taskId));
            return;
        }
        if (empty($task)) {
            $this->returnError(__('unkown task id %s', $taskId));
            return;
        }
        try {
            $taskService->updateTaskStatus($taskId, Task::STATUS_ARCHIVED);
            $task = $taskService->getTask($taskId);
            $this->returnJson([
                'success' => true,
                'data' => [
                    'id' => $taskId,
                    'status' => $task->getStatus(),
                ],
            ]);
            return;
        } catch (\Throwable $e) {
            $this->returnError(__('impossible to update task status'));
            return;
        }
    }

    public function downloadTask(): void
    {
        if ($this->hasRequestParameter('taskId')) {
            /** @var \oat\Taskqueue\JsonTask */
            $task = $this->getTask($this->getRequestParameter('taskId'));
            $report = $task->getReport();
            $report = \common_report_Report::jsonUnserialize($report);

            if ($report !== null) {
                $filename = '';
                /** @var \common_report_Report $success */
                foreach ($report->getSuccesses() as $success) {
                    if (($filename = $success->getData()) !== null) {
                        break;
                    }
                }
                /** @var FileSystemService $fileSystem */
                $fileSystem = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
                $directory = $fileSystem->getDirectory('taskQueueStorage');
                $file = $directory->getFile($filename);
                header('Set-Cookie: fileDownload=true');
                setcookie('fileDownload', 'true', 0, '/');

                //file meta
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Type: ' . $file->getMimeType());

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
