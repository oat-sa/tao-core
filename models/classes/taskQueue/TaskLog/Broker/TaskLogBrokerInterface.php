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
    const DEFAULT_CONTAINER_NAME = 'task_log';

    const COLUMN_ID = 'id';
    const COLUMN_PARENT_ID = 'parent_id';
    const COLUMN_MASTER_STATUS = 'master_status';
    const COLUMN_TASK_NAME = 'task_name';
    const COLUMN_PARAMETERS = 'parameters';
    const COLUMN_LABEL = 'label';
    const COLUMN_STATUS = 'status';
    const COLUMN_OWNER = 'owner';
    const COLUMN_REPORT = 'report';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_UPDATED_AT = 'updated_at';

    /**
     * Creates the container where the task logs will be stored.
     *
     * @return void
     */
    public function createContainer();

    /**
     * RDS table name.
     *
     * @return string
     */
    public function getTableName();

    /**
     * Inserts a new task log with status for a task.
     *
     * @param TaskInterface $task
     * @param string        $status
     * @param null|string   $label
     * @return void
     */
    public function add(TaskInterface $task, $status, $label = null);

    /**
     * Update the status of a task.
     *
     * The previous status can be used for querying the record.
     *
     * @param string      $taskId
     * @param string      $newStatus
     * @param string|null $prevStatus
     * @return int count of touched records
     */
    public function updateStatus($taskId, $newStatus, $prevStatus = null);

    /**
     * Gets the status of a task.
     *
     * @param string $taskId
     * @return string
     */
    public function getStatus($taskId);

    /**
     * Add a report for a task. New status can be supplied as well.
     *
     * @param string      $taskId
     * @param Report      $report
     * @param null|string $newStatus
     * @return int
     */
    public function addReport($taskId, Report $report, $newStatus = null);

    /**
     * Gets a report for a task.
     *
     * @param string $taskId
     * @return Report|null
     */
    public function getReport($taskId);

    /**
     * Search for task logs by defined filters.
     *
     * @param TaskLogFilter $filter
     * @return CollectionInterface|EntityInterface[]
     */
    public function search(TaskLogFilter $filter);

    /**
     * Counts task logs by defined filters.
     *
     * @param TaskLogFilter $filter
     * @return int
     */
    public function count(TaskLogFilter $filter);

    /**
     * @param TaskLogFilter $filter
     * @return TasksLogsStats
     */
    public function getStats(TaskLogFilter $filter);

    /**
     * Setting the status to archive, the record is kept. (Soft Delete)
     *
     * @param EntityInterface $entity
     * @return bool
     */
    public function archive(EntityInterface $entity);


    /**
     * Setting the status to cancelled, the record is kept. (Soft Delete)
     *
     * @param EntityInterface $entity
     * @return bool
     */
    public function cancel(EntityInterface $entity);

    /**
     * @param CollectionInterface $collection
     * @return int
     */
    public function archiveCollection(CollectionInterface $collection);

    /**
     * @param CollectionInterface $collection
     * @return int
     */
    public function cancelCollection(CollectionInterface $collection);

    /**
     * Delete the task log by id. (Hard Delete)
     *
     * @param string $taskId
     * @return bool
     */
    public function deleteById($taskId);
}
