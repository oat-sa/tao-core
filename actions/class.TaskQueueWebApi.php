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
 * Copyright (c) 2019-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\http\HttpJsonResponseTrait;
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
class tao_actions_TaskQueueWebApi extends tao_actions_CommonModule
{
    use FilesystemAwareTrait;
    use FileReferenceSerializerAwareTrait;
    use HttpJsonResponseTrait;

    private const PARAMETER_TASK_ID = 'taskId';
    private const PARAMETER_LIMIT = 'limit';
    private const PARAMETER_OFFSET = 'offset';
    private const ALL = 'all';

    /**
     * @throws common_exception_NotImplemented
     * @throws Exception
     */
    public function getAll(): void
    {
        $this->checkIfIsXmlHttpRequest();

        $taskLogService = $this->getTaskLogService();
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

        $this->setSuccessJsonResponse($collection->toArray());
    }

    /**
     * @throws common_exception_NotImplemented
     * @throws Exception
     */
    public function get(): void
    {
        $this->checkIfIsXmlHttpRequest();

        try {
            $this->checkIfTaskIdExists();

            $taskLogService = $this->getTaskLogService();

            $entity = $taskLogService->getByIdAndUser(
                $this->getRequestParameter(self::PARAMETER_TASK_ID),
                $this->getSessionUserUri()
            );

            $this->setSuccessJsonResponse((new RedirectUrlEntityDecorator(
                    new HasFileEntityDecorator(
                        new CategoryEntityDecorator($entity, $taskLogService),
                        $this->getFileSystemService(),
                        $this->getFileReferenceSerializer()
                    ),
                    $taskLogService,
                    common_session_SessionManager::getSession()->getUser()
                ))->toArray());
        } catch (Exception $e) {
            $this->setErrorJsonResponse(
                $e instanceof common_exception_UserReadableException ? $e->getUserMessage() : $e->getMessage(),
                 $e->getCode()
            );
        }
    }

    /**
     * @throws common_exception_NotImplemented
     * @throws Exception
     */
    public function stats(): void
    {
        $this->checkIfIsXmlHttpRequest();

        $this->setSuccessJsonResponse(
            $this->getTaskLogService()->getStats($this->getSessionUserUri())->toArray()
        );
    }

    /**
     * @throws common_exception_NotImplemented
     * @throws Exception
     */
    public function archive()
    {
        $this->checkIfIsXmlHttpRequest();

        try {
            $this->checkIfTaskIdExists();
            $taskIds = $this->detectTaskIds();

            $taskLogService = $this->getTaskLogService();

            $filter = $taskIds === static::ALL
                ? (new TaskLogFilter())->availableForArchived($this->getSessionUserUri())
                : (new TaskLogFilter())->addAvailableFilters($this->getSessionUserUri())->in(TaskLogBrokerInterface::COLUMN_ID, $taskIds);

            return $this->returnJson([
                'success' => (bool) $taskLogService->archiveCollection($taskLogService->search($filter))
            ]);
        } catch (Exception $e) {
             $this->setErrorJsonResponse(
                 $e instanceof common_exception_UserReadableException ? $e->getUserMessage() : $e->getMessage(),
                $e instanceof \common_exception_NotFound ? 404 : $e->getCode()
            );
        }
    }

    /**
     * @throws Exception
     */
    public function cancel()
    {
        $this->checkIfIsXmlHttpRequest();

        try {
            $this->checkIfTaskIdExists();
            $taskIds = $this->detectTaskIds();

            $taskLogService = $this->getTaskLogService();

            $filter = $taskIds === static::ALL
                ? (new TaskLogFilter())->availableForCancelled($this->getSessionUserUri())
                : (new TaskLogFilter())->addAvailableFilters($this->getSessionUserUri())->in(TaskLogBrokerInterface::COLUMN_ID, $taskIds);

            return $this->returnJson([
                'success' => (bool) $taskLogService->cancelCollection($taskLogService->search($filter))
            ]);
        } catch (Exception $e) {
            $this->setErrorJsonResponse(
                $e instanceof common_exception_UserReadableException ? $e->getUserMessage() : $e->getMessage(),
                $e instanceof \common_exception_NotFound ? 404 : $e->getCode()
            );
        }
    }

    /**
     * Download the file created by task.
     */
    public function download()
    {
        try {
            $this->checkIfTaskIdExists();

            $taskLogEntity = $this->getTaskLogService()->getByIdAndUser(
                $this->getRequestParameter(self::PARAMETER_TASK_ID),
                $this->getSessionUserUri()
            );

            if (!$taskLogEntity->getStatus()->isCompleted()) {
                throw new \common_Exception('Task "' . $taskLogEntity->getId() . '" is not downloadable.');
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
        } catch (Exception $e) {
            $this->setErrorJsonResponse(
                $e instanceof common_exception_UserReadableException ? $e->getUserMessage() : $e->getMessage(),
                 $e->getCode()
            );
        }
    }

    /**
     * @throws common_exception_MissingParameter
     */
    protected function checkIfTaskIdExists(): void
    {
        if (!$this->hasRequestParameter(self::PARAMETER_TASK_ID)) {
            throw new common_exception_MissingParameter(self::PARAMETER_TASK_ID, $this->getRequestURI());
        }
    }

    /**
     * @throws Exception
     */
    protected function checkIfIsXmlHttpRequest(): void
    {
        if (!$this->isXmlHttpRequest()) {
            throw new Exception('Only ajax call allowed.');
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
        } elseif ($taskIdsParams === static::ALL) {
            return static::ALL;
        } else {
            return [$taskIdsParams];
        }
    }

    /**
     * @throws common_exception_Error
     */
    protected function getSessionUserUri(): string
    {
        return $this->getSession()->getUserUri();
    }

    protected function getFileReferenceSerializer(): FileReferenceSerializer
    {
        return $this->getServiceLocator()->get(FileReferenceSerializer::SERVICE_ID);
    }

    protected function getFileSystemService(): FileSystemService
    {
        return $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
    }

    protected function getTaskLogService(): TaskLogInterface
    {
        return $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);
    }
}
