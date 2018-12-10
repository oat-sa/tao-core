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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\filesystem\File;
use oat\oatbox\task\Task;
use oat\tao\model\TaskQueueActionTrait;
use oat\oatbox\task\Queue;
use oat\oatbox\task\implementation\SyncQueue;

/**
 * @deprecated since version 21.7.0, to be removed in 22.0.
 */
class tao_actions_QueueAction extends \tao_actions_SaSModule
{

    use TaskQueueActionTrait;

    /**
     * @var Queue
     */
    protected $queueService;

    /**
     * @return Queue
     */
    protected function getQueueService()
    {
        if (!$this->queueService) {
            $this->queueService = $this->getServiceLocator()->get(Queue::SERVICE_ID);
        }
        return $this->queueService;
    }

    /**
     * Checks if the queue manager is asynchronous
     * @return bool
     */
    protected function isAsyncQueue()
    {
        $queue = $this->getQueueService();
        return !($queue instanceof SyncQueue);
    }

    /**
     * @param Task $task
     * @return common_report_Report
     */
    protected function getTaskReport(Task $task)
    {
        $status = $task->getStatus();
        if ($status === Task::STATUS_FINISHED || $status === Task::STATUS_ARCHIVED) {
            $report = $task->getReport();
        } else {
            $report = \common_report_Report::createInfo(__('task created'));
        }
        return $report;
    }

    /**
     * Sets the headers to download a file
     * @param string $fileName
     * @param string $mimeType
     */
    protected function prepareDownload($fileName, $mimeType)
    {
        //used by jquery file download to find out the download has been triggered ...
        header('Set-Cookie: fileDownload=true');
        setcookie('fileDownload', 'true', 0, '/');

        //file meta
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Type: ' . $mimeType);
    }

    /**
     * Extracts the path of the file attached to a report
     * @param common_report_Report $report
     * @return mixed|null
     */
    protected function getReportAttachment(common_report_Report $report)
    {
        $filename = null;
        /** @var common_report_Report $success */
        foreach ($report->getSuccesses() as $success) {
            if (!is_null($filename = $success->getData())) {
                if (is_array($filename)) {
                    $filename = $filename['uriResource'];
                }
                break;
            }
        }
        return $filename;
    }

    /**
     * Gets file from URI
     * @param string $fileUri
     * @return File
     */
    protected function getFile($fileUri)
    {
        /* @var \oat\oatbox\filesystem\FileSystemService $fileSystemService */
        $fileSystemService     = $this->getServiceLocator()->get(\oat\oatbox\filesystem\FileSystemService::SERVICE_ID);
        $storageService        = $fileSystemService->getFileSystem(Queue::FILE_SYSTEM_ID);

        return $storageService->readStream($fileUri);
    }

}