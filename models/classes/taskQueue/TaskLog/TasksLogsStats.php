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
 * Copyright (c) 2017-2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\tao\model\taskQueue\TaskLog;

use JsonSerializable;

class TasksLogsStats implements JsonSerializable
{
    public const COMPLETED_TASKS = 'completedtasks';
    public const FAILED_TASKS = 'failedtasks';
    public const IN_PROGRESS_TASKS = 'inprogresstasks';

    private int $numberOfTasksCompleted;
    private int $numberOfTasksFailed;
    private int $numberOfTasksInProgress;

    public function __construct(int $numberOfTasksCompleted, int $numberOfTasksFailed, int $numberOfTasksInProgress)
    {
        $this->numberOfTasksCompleted = $numberOfTasksCompleted;
        $this->numberOfTasksFailed = $numberOfTasksFailed;
        $this->numberOfTasksInProgress = $numberOfTasksInProgress;
    }

    public static function buildFromArray(array $rawData): TasksLogsStats
    {
        return new self(
            $rawData[static::COMPLETED_TASKS],
            $rawData[static::FAILED_TASKS],
            $rawData[static::IN_PROGRESS_TASKS]
        );
    }

    public function getNumberOfTasksCompleted(): int
    {
        return $this->numberOfTasksCompleted;
    }

    public function getNumberOfTasksFailed(): int
    {
        return $this->numberOfTasksFailed;
    }

    public function getNumberOfTasksInProgress(): int
    {
        return $this->numberOfTasksInProgress;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return [
            'numberOfTasksCompleted' => $this->numberOfTasksCompleted,
            'numberOfTasksFailed' => $this->numberOfTasksFailed,
            'numberOfTasksInProgress' => $this->numberOfTasksInProgress,
        ];
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}
