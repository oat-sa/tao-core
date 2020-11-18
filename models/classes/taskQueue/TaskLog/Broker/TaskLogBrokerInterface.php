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
 * Copyright (c) 2017-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\taskQueue\TaskLog\Broker;

use common_report_Report as Report;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLog\CollectionInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLog\TasksLogsStats;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
interface TaskLogBrokerInterface extends ServiceLocatorAwareInterface
{
    public const DEFAULT_CONTAINER_NAME = 'task_log';

    public const COLUMN_ID = 'id';
    public const COLUMN_PARENT_ID = 'parent_id';
    public const COLUMN_MASTER_STATUS = 'master_status';
    public const COLUMN_TASK_NAME = 'task_name';
    public const COLUMN_PARAMETERS = 'parameters';
    public const COLUMN_LABEL = 'label';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_OWNER = 'owner';
    public const COLUMN_REPORT = 'report';
    public const COLUMN_CREATED_AT = 'created_at';
    public const COLUMN_UPDATED_AT = 'updated_at';

    /**
     * Creates the container where the task logs will be stored.
     */
    public function createContainer(): void;

    /**
     * RDS table name.
     */
    public function getTableName(): string;

    /**
     * Inserts a new task log with status for a task.
     */
    public function add(TaskInterface $task, string $status, string $label = null): void;

    /**
     * Update the status of a task.
     *
     * The previous status can be used for querying the record.
     *
     * @return int count of touched records
     */
    public function updateStatus(string $taskId, string $newStatus, string $prevStatus = null): int;

    /**
     * Gets the status of a task.
     */
    public function getStatus(string $taskId): string;

    /**
     * Add a report for a task. New status can be supplied as well.
     */
    public function addReport(string $taskId, Report $report, string $newStatus = null): int;

    /**
     * Gets a report for a task.
     */
    public function getReport(string $taskId): ?Report;

    /**
     * Search for task logs by defined filters.
     * @return CollectionInterface|EntityInterface[]
     */
    public function search(TaskLogFilter $filter): iterable;

    /**
     * Counts task logs by defined filters.
     */
    public function count(TaskLogFilter $filter): int;

    public function getStats(TaskLogFilter $filter): TasksLogsStats;

    /**
     * Setting the status to archive, the record is kept. (Soft Delete)
     */
    public function archive(EntityInterface $entity): bool;

    /**
     * Setting the status to cancelled, the record is kept. (Soft Delete)
     */
    public function cancel(EntityInterface $entity): bool;

    public function archiveCollection(CollectionInterface $collection): int;

    public function cancelCollection(CollectionInterface $collection): int;

    /**
     * Delete the task log by id. (Hard Delete)
     */
    public function deleteById(string $taskId): bool;
}
