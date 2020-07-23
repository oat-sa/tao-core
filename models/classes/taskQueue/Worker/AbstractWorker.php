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

use common_report_Report as Report;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\session\StatelessSession;
use oat\tao\model\taskQueue\QueuerInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\RemoteTaskSynchroniserInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLog\CategorizedStatus;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\oatbox\service\ServiceManagerAwareInterface;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\generis\model\user\UserFactoryServiceInterface;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\session\SessionService;

abstract class AbstractWorker implements WorkerInterface, ServiceManagerAwareInterface
{
    use LoggerAwareTrait;
    use ServiceManagerAwareTrait;
    use OntologyAwareTrait;

    /**
     * @var QueuerInterface
     */
    protected $queuer;

    /**
     * @var TaskLogInterface
     */
    protected $taskLog;

    public function __construct(QueuerInterface $queuer, TaskLogInterface $taskLog)
    {
        $this->taskLog = $taskLog;
        $this->queuer = $queuer;
    }

    /**
     * Because of BC, it is kept as public, later it can be set to protected.
     *
     * @param TaskInterface $task
     * @return string
     * @throws \common_exception_NotFound
     */
    public function processTask(TaskInterface $task)
    {
        if (!$this->isTaskCancelled($task)) {
            $report = Report::createInfo(__('Running task %s', $task->getId()));
            try {
                $this->startUserSession($task);
                $this->logInfo('Processing task ' . $task->getId(), $this->getLogContext());

                //Database operation in task log
                $rowsTouched = $this->taskLog->setStatus($task->getId(), TaskLogInterface::STATUS_RUNNING, TaskLogInterface::STATUS_DEQUEUED);

                // if the task is being executed by another worker, just return, no report needs to be saved
                if (!$rowsTouched) {
                    $this->logInfo('Task ' . $task->getId() . ' seems to be processed by another worker.', $this->getLogContext());
                    return TaskLogInterface::STATUS_UNKNOWN;
                }

                // let the task know that it is called from a worker
                $task->applyWorkerContext();

                // execute the task
                $taskReport = $task();

                $this->logInfo('Task ' . $task->getId() . ' has been processed.', $this->getLogContext());

                if (!$taskReport instanceof Report) {
                    $this->logWarning('Task ' . $task->getId() . ' should return a report object.', $this->getLogContext());
                    //todo: isn't this message confusinig?
                    $taskReport = Report::createInfo(__('Task not returned any report.'));
                }

                $report->add($taskReport);

                unset($taskReport, $rowsTouched);
            } catch (\Error $e) {
                $this->logCritical('Executing task ' . $task->getId() . ' failed with MSG: ' . $e->getMessage(), $this->getLogContext());
                $report = Report::createFailure(__('Executing task %s failed', $task->getId()));
            } catch (\Exception $e) {
                $this->logError('Executing task ' . $task->getId() . ' failed with MSG: ' . $e->getMessage(), $this->getLogContext());
                $report = Report::createFailure(__('Executing task %s failed', $task->getId()));
            }

            // Initializing status
            $status = $report->getType() == Report::TYPE_ERROR || $report->containsError()
                ? TaskLogInterface::STATUS_FAILED
                : TaskLogInterface::STATUS_COMPLETED;

            // Change the status if the task has children
            if ($task->hasChildren() && $status == TaskLogInterface::STATUS_COMPLETED) {
                $status = TaskLogInterface::STATUS_CHILD_RUNNING;
            }

            $cloneCreated = false;

            // if the task is a special sync task: the status of the parent task depends on the status of the remote task.
            if ($this->isRemoteTaskSynchroniser($task) && $status == TaskLogInterface::STATUS_COMPLETED) {
                // if the remote task is still in progress, we have to reschedule this task
                // the RESTApi returns TaskLogCategorizedStatus values
                if (in_array($this->getRemoteStatus($task), [CategorizedStatus::STATUS_CREATED, CategorizedStatus::STATUS_IN_PROGRESS])) {
                    if ($this->queuer->count() <= 1) {
                        //if there is less than or exactly one task in the queue, let's sleep a bit, in order not to regenerate the same task too much
                        sleep(3);
                    }

                    $cloneCreated = $this->queuer->enqueue(clone $task, $task->getLabel());
                } elseif ($this->getRemoteStatus($task) == CategorizedStatus::STATUS_FAILED) {
                    // if the remote task status is failed
                    $status = TaskLogInterface::STATUS_FAILED;
                }
            }

            if (!$cloneCreated) {
                $this->taskLog->setReport($task->getId(), $report, $status);
            } else {
                // if there is a clone, delete the old task log
                //TODO: once we have the centralized way of cleaning up the log table, this should be refactored
                $this->taskLog->getBroker()->deleteById($task->getId());
            }

            // Update parent
            if ($task->hasParent()) {
                /** @var EntityInterface $parentLogTask */
                $parentLogTask = $this->taskLog->getById($task->getParentId());
                if (!$parentLogTask->isMasterStatus()) {
                    $this->taskLog->updateParent($task->getParentId());
                }
            }

            unset($report);
        } else {
            $this->taskLog->setReport(
                $task->getId(),
                Report::createInfo(__('Task %s has been cancelled, message was not processed.', $task->getId())),
                TaskLogInterface::STATUS_CANCELLED
            );

            $status = TaskLogInterface::STATUS_CANCELLED;
        }

        // delete message from queue
        $this->queuer->acknowledge($task);

        return $status;
    }

    protected function getLogContext()
    {
        return [];
    }

    /**
     * @param TaskInterface $task
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     * @throws \Exception
     */
    private function startUserSession(TaskInterface $task)
    {
        /** @var SessionService $sessionService */
        $sessionService = $this->getServiceLocator()->get(SessionService::class);
        if ($task->getOwner() && $sessionService->getCurrentSession()->getUser()->getIdentifier() !== $task->getOwner()) {
            $user = $this->getUserFactoryService()->createUser($this->getResource($task->getOwner()));
            $session = new StatelessSession($user);
            $sessionService->setSession($session);
        } else if (!$task->getOwner()) {
            \common_session_SessionManager::endSession();
        }
    }

    /**
     * @param TaskInterface $task
     * @return bool
     */
    private function isRemoteTaskSynchroniser(TaskInterface $task)
    {
        return $task instanceof RemoteTaskSynchroniserInterface || ($task instanceof CallbackTaskInterface && $task->getCallable() instanceof RemoteTaskSynchroniserInterface);
    }

    /**
     * @param TaskInterface|RemoteTaskSynchroniserInterface $task
     * @return mixed
     */
    private function getRemoteStatus(TaskInterface $task)
    {
        return $task instanceof CallbackTaskInterface ? $task->getCallable()->getRemoteStatus() : $task->getRemoteStatus();
    }

    /**
     * @param TaskInterface $task
     * @return bool
     */
    private function isTaskCancelled(TaskInterface $task)
    {
        return $this->taskLog->getStatus($task->getId()) === TaskLogInterface::STATUS_CANCELLED;
    }

    /**
     * @return UserFactoryServiceInterface
     */
    private function getUserFactoryService()
    {
        return $this->getServiceLocator()->get(UserFactoryServiceInterface::SERVICE_ID);
    }
}
