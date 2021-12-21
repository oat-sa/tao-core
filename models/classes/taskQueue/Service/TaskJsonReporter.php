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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\tao\model\taskQueue\Service;

use common_exception_BadRequest;
use common_session_SessionManager;
use oat\oatbox\reporting\ReportInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLogInterface;

class TaskJsonReporter
{
    /** @var TaskLogInterface */
    private $taskLog;

    public function __construct(TaskLogInterface $taskLog)
    {
        $this->taskLog = $taskLog;
    }

    public function report(TaskInterface $task, array $extraData = []): array
    {
        return [
            'extra' => $extraData,
            'task' => $this->getTaskLogReturnData($task->getId())
        ];
    }

    private function getTaskLogEntity(string $taskId, string $userId = null)
    {
        if (is_null($userId)) {
            $userId = $this->getUserId();
        }

        return $this->taskLog->getByIdAndUser($taskId, $userId);
    }

    private function getUserId(): string
    {
        return common_session_SessionManager::getSession()->getUserUri();
    }

    private function getTaskLogReturnData(string $taskId, string $forcedTaskType = null, string $userId = null): array
    {
        $taskLogEntity = $this->getTaskLogEntity($taskId, $userId);

        if (!is_null($forcedTaskType) && $taskLogEntity->getTaskName() !== $forcedTaskType) {
            throw new common_exception_BadRequest("Wrong task type");
        }

        $result['id'] = $taskLogEntity->getId();
        $result['status'] = $taskLogEntity->getStatus()->getLabel();
        $result['report'] = $taskLogEntity->getReport()
            ? $this->getFullReport($taskLogEntity->getReport())
            : [];

        return $result;
    }

    /**
     * @return ReportInterface[]
     */
    private function getPlainReport(ReportInterface $report): array
    {
        $reports[] = $report;

        if ($report->hasChildren()) {
            foreach ($report as $r) {
                $reports = array_merge($reports, $this->getPlainReport($r));
            }
        }

        return $reports;
    }

    private function getFullReport(ReportInterface $report): array
    {
        $reports = [];
        $plainReports = $this->getPlainReport($report);

        foreach ($plainReports as $r) {
            $reports[] = [
                'type' => $r->getType(),
                'message' => $r->getMessage(),
            ];
        }

        return $reports;
    }
}
