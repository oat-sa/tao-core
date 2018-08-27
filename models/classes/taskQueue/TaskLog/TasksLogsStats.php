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

namespace oat\tao\model\taskQueue\TaskLog;

use JsonSerializable;

class TasksLogsStats implements JsonSerializable
{
    const COMPLETED_TASKS = 'completedtasks';
    const FAILED_TASKS = 'failedtasks';
    const IN_PROGRESS_TASKS = 'inprogresstasks';

    /** @var  int */
    private $numberOfTasksCompleted;

    /** @var  int */
    private $numberOfTasksFailed;

    /** @var  int */
    private $numberOfTasksInProgress;

    /**
     * TaskLogStatus constructor.
     * @param int $numberOfTasksCompleted
     * @param int $numberOfTasksFailed
     * @param int $numberOfTasksInProgress
     */
    public function __construct($numberOfTasksCompleted, $numberOfTasksFailed, $numberOfTasksInProgress)
    {
        $this->numberOfTasksCompleted = $numberOfTasksCompleted;
        $this->numberOfTasksFailed = $numberOfTasksFailed;
        $this->numberOfTasksInProgress = $numberOfTasksInProgress;
    }

    /**
     * @param array $rawData
     * @return TasksLogsStats
     */
    public static function buildFromArray(array $rawData)
    {
        return new self($rawData[static::COMPLETED_TASKS], $rawData[static::FAILED_TASKS], $rawData[static::IN_PROGRESS_TASKS]);
    }

    /**
     * @return int
     */
    public function getNumberOfTasksCompleted()
    {
        return $this->numberOfTasksCompleted;
    }

    /**
     * @return int
     */
    public function getNumberOfTasksFailed()
    {
        return $this->numberOfTasksFailed;
    }

    /**
     * @return int
     */
    public function getNumberOfTasksInProgress()
    {
        return $this->numberOfTasksInProgress;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'numberOfTasksCompleted' => $this->numberOfTasksCompleted,
            'numberOfTasksFailed' => $this->numberOfTasksFailed,
            'numberOfTasksInProgress' => $this->numberOfTasksInProgress,
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->jsonSerialize();
    }
}