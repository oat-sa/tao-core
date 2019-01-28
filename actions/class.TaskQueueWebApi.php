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

use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\taskQueue\Task\FileReferenceSerializerAwareTrait;
use oat\tao\model\taskQueue\Task\FilesystemAwareTrait;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\Decorator\CategoryEntityDecorator;
use oat\tao\model\taskQueue\TaskLog\Decorator\HasFileEntityDecorator;
use oat\tao\model\taskQueue\TaskLog\Decorator\RedirectUrlEntityDecorator;
use oat\tao\model\taskQueue\TaskLog\Decorator\SimpleManagementCollectionDecorator;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLogInterface;

/**
 * API controller to get task queue data by our WEB front-end.
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class tao_actions_TaskQueueWebApi extends \tao_actions_CommonModule
{
    use FilesystemAwareTrait;
    use FileReferenceSerializerAwareTrait;

    const PARAMETER_TASK_ID = 'taskId';
    const PARAMETER_LIMIT = 'limit';
    const PARAMETER_OFFSET = 'offset';
    const ALL = 'all';

    /**
     * @throws \common_exception_NotImplemented
     */
    public function getAll()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        /** @var TaskLogInterface $taskLogService */
        $taskLogService = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);
        $limit = $offset = null;

        if ($this->hasRequestParameter(self::PARAMETER_LIMIT)) {
            $limit = (int) $this->getRequestParameter(self::PARAMETER_LIMIT);
        }

        if ($this->hasRequestParameter(self::PARAMETER_OFFSET)) {
            $offset = (int) $this->getRequestParameter(self::PARAMETER_OFFSET);
        }

        $collection = new SimpleManagementCollectionDecorator(
            $taskLogService->findAvailableByUser($this->getSessionUserUri(), $limit, $offset),
            $taskLogService,
            $this->getFileSystemService(),
            $this->getFileReferenceSerializer(),
            false
        );

        return $this->returnJson([
            'success' => true,
            'data' => $collection->toArray()
        ]);
    }

    /**
     * @throws \common_exception_NotImplemented
     */
    public function get()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        /** @var TaskLogInterface $taskLogService */
        $taskLogService = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);

        try {
            $this->assertTaskIdExists();

            $entity = $taskLogService->getByIdAndUser(
                $this->getRequestParameter(self::PARAMETER_TASK_ID),
                $this->getSessionUserUri()
            );

            return $this->returnJson([
                'success' => true,
                'data' => (new RedirectUrlEntityDecorator(
                    new HasFileEntityDecorator(
                        new CategoryEntityDecorator($entity, $taskLogService),
                        $this->getFileSystemService(),
                        $this->getFileReferenceSerializer()
                    ),
                    $taskLogService,
                    common_session_SessionManager::getSession()->getUser()
                ))->toArray()
            ]);
        } catch (\Exception $e) {
            return $this->returnJson([
                'success' => false,
                'errorMsg' => $e instanceof \common_exception_UserReadableException ? $e->getUserMessage() : $e->getMessage(),
                'errorCode' => $e->getCode(),
            ]);
        }
    }

    /**
     * @throws \common_exception_NotImplemented
     */
    public function stats()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        /** @var TaskLogInterface $taskLogService */
        $taskLogService = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);

        return $this->returnJson([
            'success' => true,
            'data' => $taskLogService->getStats($this->getSessionUserUri())->toArray()
        ]);
    }

    /**
     * @throws \common_exception_NotImplemented
     * @throws \Exception
     */
    public function archive()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        try {
            $this->assertTaskIdExists();
            $taskIds = $this->detectTaskIds();

            /** @var TaskLogInterface $taskLogService */
            $taskLogService = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);

            if ($taskIds === static::ALL) {
                $filter = (new TaskLogFilter())->availableForArchived($this->getSessionUserUri());
                $taskLogCollection = $taskLogService->search($filter);
            } else {
                $filter = (new TaskLogFilter())->addAvailableFilters($this->getSessionUserUri())->in(TaskLogBrokerInterface::COLUMN_ID, $taskIds);
                $taskLogCollection = $taskLogService->search($filter);
            }

            $hasBeenArchive = $taskLogService->archiveCollection($taskLogCollection);
            return $this->returnJson([
                'success' => (bool)$hasBeenArchive
            ]);
        } catch (\Exception $e) {
            return $this->returnJson([
                'success' => false,
                'errorMsg' => $e instanceof \common_exception_UserReadableException ? $e->getUserMessage() : $e->getMessage(),
                'errorCode' => $e instanceof \common_exception_NotFound ? 404 : $e->getCode(),
            ]);
        }
    }

    /**
     * @throws \Exception
     */
    public function cancel()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        try {
            $this->assertTaskIdExists();
            $taskIds = $this->detectTaskIds();

            /** @var TaskLogInterface $taskLogService */
            $taskLogService = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);

            if ($taskIds === static::ALL) {
                $filter = (new TaskLogFilter())->availableForCancelled($this->getSessionUserUri());
                $taskLogCollection = $taskLogService->search($filter);
            } else {
                $filter = (new TaskLogFilter())->addAvailableFilters($this->getSessionUserUri())->in(TaskLogBrokerInterface::COLUMN_ID, $taskIds);
                $taskLogCollection = $taskLogService->search($filter);
            }

            return $this->returnJson([
                'success' => (bool) $taskLogService->cancelCollection($taskLogCollection)
            ]);
        } catch (\Exception $e) {
            return $this->returnJson([
                'success' => false,
                'errorMsg' => $e instanceof \common_exception_UserReadableException ? $e->getUserMessage() : $e->getMessage(),
                'errorCode' => $e instanceof \common_exception_NotFound ? 404 : $e->getCode(),
            ]);
        }
    }

    /**
     * Download the file created by task.
     */
    public function download()
    {
        try{
            $this->assertTaskIdExists();

            /** @var TaskLogInterface $taskLogService */
            $taskLogService = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);

            $taskLogEntity = $taskLogService->getByIdAndUser($this->getRequestParameter(self::PARAMETER_TASK_ID), $this->getSessionUserUri());

            if (!$taskLogEntity->getStatus()->isCompleted()) {
                throw new \common_Exception('Task "'. $taskLogEntity->getId() .'" is not downloadable.');
            }

            $fileNameOrSerial = $taskLogEntity->getFileNameFromReport();

            if (empty($fileNameOrSerial)) {
                throw new \common_Exception('Filename not found in report.');
            }

            // first try to get a referenced file is it is a serial
            $file = $this->getReferencedFile($fileNameOrSerial);

            // if no file let's try the task queue storage
            if (is_null($file)) {
                $file = $this->getQueueStorageFile($fileNameOrSerial);
            }

            if (!$file) {
                throw new \common_exception_NotFound('File not found.');
            }

            header('Set-Cookie: fileDownload=true');
            setcookie('fileDownload', 'true', 0, '/');
            header('Content-Disposition: attachment; filename="' . $file->getBasename() . '"');
            header('Content-Type: ' . $file->getMimeType());

            \tao_helpers_Http::returnStream($file->readPsrStream());
            exit();
        } catch (\Exception $e) {
            return $this->returnJson([
                'success' => false,
                'errorMsg' => $e instanceof \common_exception_UserReadableException ? $e->getUserMessage() : $e->getMessage(),
                'errorCode' => $e->getCode(),
            ]);
        }
    }

    /**
     * @throws \common_exception_MissingParameter
     */
    protected function assertTaskIdExists()
    {
        if (!$this->hasRequestParameter(self::PARAMETER_TASK_ID)) {
            throw new \common_exception_MissingParameter(self::PARAMETER_TASK_ID, $this->getRequestURI());
        }
    }

    /**
     * @return array|string
     */
    protected function detectTaskIds()
    {
        $taskIdsParams = $this->getRequestParameter(self::PARAMETER_TASK_ID);

        if (is_array($taskIdsParams)) {
            return $taskIdsParams;
        } else if ($taskIdsParams === static::ALL) {
            return static::ALL;
        } else {
            return [$taskIdsParams];
        }
    }

    /**
     * Retrieve the user session uri
     *
     * @return string
     * @throws common_exception_Error
     */
    protected function getSessionUserUri()
    {
        return $this->getSession()->getUserUri();
    }

    /**
     * @return FileReferenceSerializer|object
     */
    protected function getFileReferenceSerializer()
    {
        return $this->getServiceLocator()->get(FileReferenceSerializer::SERVICE_ID);
    }

    /**
     * @return FileSystemService|object
     */
    protected function getFileSystemService()
    {
        return $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
    }
}
