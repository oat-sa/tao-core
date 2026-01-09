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

use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\TaskLogEntity;
use Traversable;

class TaskLogCollection implements CollectionInterface
{
    /** @var EntityInterface[]  */
    private $taskLogs = [];

    /**
     * @param EntityInterface[] $taskLogs
     */
    public function __construct(array $taskLogs)
    {
        $this->taskLogs = $taskLogs;
    }

    /**
     * @param array $rows
     * @return TaskLogCollection
     *
     * @throws \Exception
     */
    public static function createFromArray(array $rows, string $dateFormat)
    {
        $logs = [];

        foreach ($rows as $row) {
            $logs[] = TaskLogEntity::createFromArray($row, $dateFormat);
        }

        return new static($logs);
    }

    /**
     * @return TaskLogCollection
     */
    public static function createEmptyCollection()
    {
        return new static([]);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach ($this->taskLogs as $taskLog) {
            $data[] = $taskLog->toArray();
        }

        return $data;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->taskLogs);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->taskLogs);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->taskLogs);
    }

    /**
     * @return TaskLogEntity
     */
    public function first()
    {
        return reset($this->taskLogs);
    }

    /**
     * @return TaskLogEntity
     */
    public function last()
    {
        return end($this->taskLogs);
    }

    /**
     * @inheritdoc
     */
    public function getIds()
    {
        $ids = [];
        /** @var EntityInterface $item */
        foreach ($this->taskLogs as $item) {
            $ids[] = $item->getId();
        }

        return $ids;
    }
}
