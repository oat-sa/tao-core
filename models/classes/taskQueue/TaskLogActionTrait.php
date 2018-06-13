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

namespace oat\tao\model\taskQueue;

use common_report_Report as Report;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;

/**
 * Helper trait for actions/controllers to operate with task log data for a given task.
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
trait TaskLogActionTrait
{
    /**
     * @return ServiceManager
     */
    abstract protected function getServiceManager();

    /**
     * @param array $data
     * @param int $httpStatus
     * @return mixed
     */
    abstract protected function returnJson($data, $httpStatus = 200);

    /**
     * @param string $taskId
     * @param string $userId
     * @return EntityInterface
     */
    protected function getTaskLogEntity($taskId, $userId = null)
    {
        /** @var TaskLogInterface $taskLog */
        $taskLog = $this->getServiceManager()->get(TaskLogInterface::SERVICE_ID);

        if (is_null($userId)) {
            $userId = $this->getUserId();
        }

        return $taskLog->getByIdAndUser((string) $taskId, (string) $userId);
    }

    /**
     * Get default user id.
     *
     * @return string
     */
    protected function getUserId()
    {
        return \common_session_SessionManager::getSession()->getUserUri();
    }

    /**
     * @param string $taskId
     * @param string|null $forcedTaskType
     * @param string|null $userId
     * @return array
     * @throws \common_exception_BadRequest
     */
    protected function getTaskLogReturnData($taskId, $forcedTaskType = null, $userId = null)
    {
        $taskLogEntity = $this->getTaskLogEntity($taskId, $userId);

        if (!is_null($forcedTaskType) && $taskLogEntity->getTaskName() !== $forcedTaskType) {
            throw new \common_exception_BadRequest("Wrong task type");
        }

        $result['id']     = $this->getTaskId($taskLogEntity);
        $result['status'] = $this->getTaskStatus($taskLogEntity);
        $result['report'] = $taskLogEntity->getReport() ? $this->getTaskReport($taskLogEntity) : [];

        return array_merge($result, (array) $this->addExtraReturnData($taskLogEntity));
    }

    /**
     * Returns task data in a specific data structure required by the front-end component.
     *
     * @param TaskInterface $task
     * @param array         $extraData
     * @return mixed
     */
    protected function returnTaskJson(TaskInterface $task, array $extraData = [])
    {
        return $this->returnJson([
            'success' => true,
            'data' => [
                'extra' => $extraData,
                'task' => $this->getTaskLogReturnData($task->getId())
            ]
        ]);
    }

    /**
     * Return task identifier
     *
     * @param EntityInterface $taskLogEntity
     * @return string
     */
    protected function getTaskId(EntityInterface $taskLogEntity)
    {
        return $taskLogEntity->getId();
    }

    /**
     * @param EntityInterface $taskLogEntity
     * @return string
     */
    protected function getTaskStatus(EntityInterface $taskLogEntity)
    {
        return $taskLogEntity->getStatus()->getLabel();
    }

    /**
     * As default, it returns the reports as an associative array.
     *
     * @param EntityInterface $taskLogEntity
     * @return array
     */
    protected function getTaskReport(EntityInterface $taskLogEntity)
    {
        return $this->getReportAsAssociativeArray($taskLogEntity->getReport());
    }

    /**
     * @return array
     */
    protected function addExtraReturnData(EntityInterface $taskLogEntity)
    {
        return [];
    }

    /**
     * @param Report $report
     * @return Report[]
     */
    protected function getPlainReport(Report $report)
    {
        $reports[] = $report;

        if ($report->hasChildren()) {
            foreach ($report as $r) {
                $reports = array_merge($reports, $this->getPlainReport($r));
            }
        }

        return $reports;
    }

    /**
     * @param Report $report
     * @return array
     */
    protected function getReportAsAssociativeArray(Report $report)
    {
        $reports = [];
        $plainReports = $this->getPlainReport($report);

        foreach ($plainReports as $r) {
            $reports[] = [
                'type'    => $r->getType(),
                'message' => $r->getMessage(),
            ];
        }

        return $reports;
    }
}