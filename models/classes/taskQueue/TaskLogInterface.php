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
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\DataTablePayload;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLog\CollectionInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLog\TasksLogsStats;
use oat\tao\model\datatable\DatatableRequest as DatatableRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Datetime;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
interface TaskLogInterface extends LoggerAwareInterface
{
    public const SERVICE_ID = 'tao/taskLog';

    public const OPTION_TASK_LOG_BROKER = 'task_log_broker';

    /**
     * An array of tasks names with the specified category.
     */
    public const OPTION_TASK_TO_CATEGORY_ASSOCIATIONS = 'task_to_category_associations';

    public const OPTION_TASK_IGNORE_LIST = 'task_ui_ignore_list';

    public const STATUS_ENQUEUED = 'enqueued';
    public const STATUS_DEQUEUED = 'dequeued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_CHILD_RUNNING = 'child_running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_UNKNOWN = 'unknown';

    public const CATEGORY_UNKNOWN = 'unknown';
    public const CATEGORY_IMPORT = 'import';
    public const CATEGORY_EXPORT = 'export';
    public const CATEGORY_DELIVERY_COMPILATION = 'delivery_comp';
    public const CATEGORY_CREATE = 'create';
    public const CATEGORY_UPDATE = 'update';
    public const CATEGORY_DELETE = 'delete';
    public const CATEGORY_COPY = 'copy';
    public const CATEGORY_UNRELATED_RESOURCE = 'unrelated_resource';

    public const DEFAULT_LIMIT = 20;

    public const OPTION_DEFAULT_BATCH_SIZE = 'default_batch_size';
    public const DEFAULT_BATCH_SIZE = 1000;

    /**
     * It's not related to the user management, just a placeholder to distinguish the user running the script from CLI.
     */
    public const SUPER_USER = 'cli-user';

    /**
     * @return void
     */
    public function createContainer();

    /**
     * Insert a new task with status into the container.
     *
     * @param TaskInterface $task
     * @param string        $status
     * @param null|string   $label
     * @return TaskLogInterface
     */
    public function add(TaskInterface $task, $status, $label = null);

    /**
     * Set a status for a task.
     *
     * @param string $taskId
     * @param string $newStatus
     * @param string|null $prevStatus
     * @return int
     */
    public function setStatus($taskId, $newStatus, $prevStatus = null);

    /**
     * Gets the status of a task.
     *
     * @param string $taskId
     * @return string
     */
    public function getStatus($taskId);

    /**
     * Saves the report, status and redirect url for a task.
     *
     * @param string $taskId
     * @param Report $report
     * @param string|null $newStatus
     * @return TaskLogInterface
     */
    public function setReport($taskId, Report $report, $newStatus = null);

    /**
     * Gets the report for a task if that exists.
     *
     * @param string $taskId
     * @return Report|null
     */
    public function getReport($taskId);

    /**
     * Updates the parent task.
     *
     * @param string $parentTaskId
     * @return TaskLogInterface
     */
    public function updateParent($parentTaskId);

    /**
     * @param TaskLogFilter $filter
     * @return CollectionInterface|EntityInterface[]
     */
    public function search(TaskLogFilter $filter);

    /**
     * @param TaskLogFilter             $filter
     * @param DatatableRequestInterface $request
     * @return DataTablePayload
     */
    public function getDataTablePayload(TaskLogFilter $filter, DatatableRequestInterface $request);

    /**
     * @param string $userId
     * @param null   $limit
     * @param null   $offset
     * @return CollectionInterface|EntityInterface[]
     */
    public function findAvailableByUser($userId, $limit = null, $offset = null);

    /**
     * @param string $userId
     * @return TasksLogsStats
     */
    public function getStats($userId);

    /**
     * @param string $taskId
     * @return EntityInterface
     *
     * @throws \common_exception_NotFound
     */
    public function getById($taskId);

    /**
     * @param string $taskId
     * @param string $userId
     * @param bool   $archivedAllowed
     * @return EntityInterface
     *
     */
    public function getByIdAndUser($taskId, $userId, $archivedAllowed = false);

    /**
     * @param EntityInterface $entity
     * @param bool $forceArchive
     * @return bool
     *
     * @throws \Exception
     */
    public function archive(EntityInterface $entity, $forceArchive = false);

    /**
     * @param CollectionInterface $collection
     * @param bool                $forceArchive
     * @return bool
     */
    public function archiveCollection(CollectionInterface $collection, $forceArchive = false);

    /**
     * @param EntityInterface $entity
     * @param bool            $forceCancel
     * @return bool
     *
     * @throws \Exception
     */
    public function cancel(EntityInterface $entity, $forceCancel = false);

    /**
     * @param CollectionInterface $collection
     * @param bool                $forceCancel
     * @return bool
     */
    public function cancelCollection(CollectionInterface $collection, $forceCancel = false);

    /**
     * Gets the current broker instance.
     *
     * @return TaskLogBrokerInterface
     */
    public function getBroker();

    /**
     * Is the current broker RDS based?
     *
     * @return bool
     */
    public function isRds();

    /**
     * Link a task to a category.
     *
     * @param string|object $taskName
     * @param string $category
     * @return QueueDispatcherInterface
     */
    public function linkTaskToCategory($taskName, $category);

    /**
     * Returns the defined category for a task.
     *
     * @param string|object $taskName
     * @return string
     */
    public function getCategoryForTask($taskName);

    /**
     * Returns the possible categories for a task.
     *
     * @return array
     */
    public function getTaskCategories();

    /**
     * Fetch task execution times for given date range
     * Formatted with task id as key and execution time as value
     *
     * @param DateTime $from
     * @param DateTime $to
     * @return array [ 'task1'=> 'time-in-second', 'task-id-1' => 10]`
     */
    public function getTaskExecutionTimesByDateRange(DateTime $from, DateTime $to): array;
}
