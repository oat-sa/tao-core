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

use Doctrine\DBAL\Connection;
use oat\oatbox\PhpSerializable;
use common_report_Report as Report;
use Doctrine\DBAL\Query\QueryBuilder;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLog\CategorizedStatus;
use oat\tao\model\taskQueue\TaskLog\CollectionInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogCollection;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLog\TasksLogsStats;
use oat\tao\model\taskQueue\TaskLogInterface;
use Psr\Log\LoggerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use oat\oatbox\log\LoggerAwareTrait;

/**
 * Storing message logs in RDS.
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class RdsTaskLogBroker implements TaskLogBrokerInterface, PhpSerializable, LoggerAwareInterface, RdsTaskLogBrokerInterface
{
    use ServiceLocatorAwareTrait;
    use LoggerAwareTrait;

    private $persistenceId;

    /**
     * @var \common_persistence_SqlPersistence
     */
    protected $persistence;

    private $containerName;

    /**
     * RdsTaskLogBroker constructor.
     *
     * @param string $persistenceId
     * @param null $containerName
     */
    public function __construct($persistenceId, $containerName = null)
    {
        if (empty($persistenceId)) {
            throw new \InvalidArgumentException("Persistence id needs to be set for ". __CLASS__);
        }

        $this->persistenceId = $persistenceId;
        $this->containerName = empty($containerName) ? self::DEFAULT_CONTAINER_NAME : $containerName;
    }

    public function __toPhpCode()
    {
        return 'new '. get_called_class() .'('
            . \common_Utils::toHumanReadablePhpString($this->persistenceId)
            . ', '
            . \common_Utils::toHumanReadablePhpString($this->containerName)
            .')';
    }

    /**
     * @return \common_persistence_SqlPersistence
     */
    protected function getPersistence()
    {
        if (is_null($this->persistence)) {
            $this->persistence = $this->getServiceLocator()
                ->get(\common_persistence_Manager::SERVICE_ID)
                ->getPersistenceById($this->persistenceId);
        }

        return $this->persistence;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return strtolower(QueueDispatcherInterface::QUEUE_PREFIX .'_'. $this->containerName);
    }

    /**
     * @inheritdoc
     */
    public function createContainer()
    {
        /** @var \common_persistence_sql_pdo_mysql_SchemaManager $schemaManager */
        $schemaManager = $this->getPersistence()->getSchemaManager();

        $fromSchema = $schemaManager->createSchema();
        $toSchema = clone $fromSchema;

        // if our table does not exist, let's create it
        if(false === $fromSchema->hasTable($this->getTableName())) {
            $table = $toSchema->createTable($this->getTableName());
            $table->addOption('engine', 'InnoDB');
            $table->addColumn(self::COLUMN_ID, 'string', ["notnull" => true, "length" => 255]);
            $table->addColumn(self::COLUMN_PARENT_ID, 'string', ["notnull" => false, "length" => 255, "default" => null]);
            $table->addColumn(self::COLUMN_TASK_NAME, 'string', ["notnull" => true, "length" => 255]);
            $table->addColumn(self::COLUMN_PARAMETERS, 'text', ["notnull" => false, "default" => null]);
            $table->addColumn(self::COLUMN_LABEL, 'string', ["notnull" => false, "length" => 255]);
            $table->addColumn(self::COLUMN_STATUS, 'string', ["notnull" => true, "length" => 50]);
            $table->addColumn(self::COLUMN_MASTER_STATUS, 'boolean', ["default" => 0]);
            $table->addColumn(self::COLUMN_OWNER, 'string', ["notnull" => false, "length" => 255, "default" => null]);
            $table->addColumn(self::COLUMN_REPORT, 'text', ["notnull" => false, "default" => null]);
            $table->addColumn(self::COLUMN_CREATED_AT, 'datetime', ['notnull' => true]);
            $table->addColumn(self::COLUMN_UPDATED_AT, 'datetime', ['notnull' => false]);
            $table->setPrimaryKey(['id']);
            $table->addIndex([self::COLUMN_TASK_NAME, self::COLUMN_OWNER], $this->getTableName() .'IDX_task_name_owner');
            $table->addIndex([self::COLUMN_STATUS], $this->getTableName() .'IDX_status');
            $table->addIndex([self::COLUMN_CREATED_AT], $this->getTableName() .'IDX_created_at');

            $queries = $this->getPersistence()->getPlatForm()->getMigrateSchemaSql($fromSchema, $toSchema);
            foreach ($queries as $query) {
                $this->getPersistence()->exec($query);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function add(TaskInterface $task, $status, $label = null)
    {
        $this->getPersistence()->insert($this->getTableName(), [
            self::COLUMN_ID   => (string) $task->getId(),
            self::COLUMN_PARENT_ID  => $task->getParentId() ? (string) $task->getParentId() : null,
            self::COLUMN_TASK_NAME => $task instanceof CallbackTaskInterface && is_object($task->getCallable()) ? get_class($task->getCallable()) : get_class($task),
            self::COLUMN_PARAMETERS => json_encode($task->getParameters()),
            self::COLUMN_LABEL => (string) $label,
            self::COLUMN_STATUS => (string) $status,
            self::COLUMN_OWNER => (string) $task->getOwner(),
            self::COLUMN_CREATED_AT => $task->getCreatedAt()->format('Y-m-d H:i:s'),
            self::COLUMN_UPDATED_AT => $this->getPersistence()->getPlatForm()->getNowExpression(),
            self::COLUMN_MASTER_STATUS => (integer) $task->isMasterStatus(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getStatus($taskId)
    {
        $qb = $this->getQueryBuilder()
            ->select(self::COLUMN_STATUS)
            ->from($this->getTableName())
            ->andWhere(self::COLUMN_ID .' = :id')
            ->setParameter('id', $taskId);

        return $qb->execute()->fetchColumn();
    }

    /**
     * @inheritdoc
     */
    public function updateStatus($taskId, $newStatus, $prevStatus = null)
    {
        $qb = $this->getQueryBuilder()
            ->update($this->getTableName())
            ->set(self::COLUMN_STATUS, ':status_new')
            ->set(self::COLUMN_UPDATED_AT, ':updated_at')
            ->where(self::COLUMN_ID .' = :id')
            ->setParameter('id', (string) $taskId)
            ->setParameter('status_new', (string) $newStatus)
            ->setParameter('updated_at', $this->getPersistence()->getPlatForm()->getNowExpression());

        if ($prevStatus) {
            $qb->andWhere(self::COLUMN_STATUS .' = :status_prev')
                ->setParameter('status_prev', (string) $prevStatus);
        }

        return $qb->execute();
    }

    /**
     * @inheritdoc
     */
    public function addReport($taskId, Report $report, $newStatus = null)
    {
        $qb = $this->getQueryBuilder()
            ->update($this->getTableName())
            ->set(self::COLUMN_REPORT, ':report')
            ->set(self::COLUMN_STATUS, ':status_new')
            ->set(self::COLUMN_UPDATED_AT, ':updated_at')
            ->andWhere(self::COLUMN_ID .' = :id')
            ->setParameter('id', (string) $taskId)
            ->setParameter('report', json_encode($report))
            ->setParameter('status_new', (string) $newStatus)
            ->setParameter('updated_at', $this->getPersistence()->getPlatForm()->getNowExpression());

        return $qb->execute();
    }

    /**
     * @inheritdoc
     */
    public function getReport($taskId)
    {
        $qb = $this->getQueryBuilder()
            ->select(self::COLUMN_REPORT)
            ->from($this->getTableName())
            ->andWhere(self::COLUMN_ID .' = :id')
            ->setParameter('id', (string) $taskId);

        if (($reportJson = $qb->execute()->fetchColumn())
            && ($reportData = json_decode($reportJson, true)) !== null
            && json_last_error() === JSON_ERROR_NONE
        ) {
            // if we have a valid JSON string and no JSON error, let's restore the report object
            return Report::jsonUnserialize($reportData);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function search(TaskLogFilter $filter)
    {
        try {
            $qb = $this->getQueryBuilder()
                ->select($filter->getColumns())
                ->from($this->getTableName());

            $qb->setMaxResults($filter->getLimit());
            $qb->setFirstResult($filter->getOffset());

            if ($filter->getSortBy()) {
                $qb->orderBy($filter->getSortBy(), $filter->getSortOrder());
            }

            $filter->applyFilters($qb);

            $collection = TaskLogCollection::createFromArray($qb->execute()->fetchAll());
        } catch (\Exception $exception) {
            $this->logError('Searching for task logs failed with MSG: ' . $exception->getMessage());

            $collection = TaskLogCollection::createEmptyCollection();
        }

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function count(TaskLogFilter $filter)
    {
        try {
            $qb = $this->getQueryBuilder()
                ->select('COUNT(*)')
                ->from($this->getTableName());

            $filter->applyFilters($qb);

            return (int) $qb->execute()->fetchColumn();
        } catch (\Exception $e) {
            $this->logError('Counting task logs failed with MSG: '. $e->getMessage());
        }

        return 0;
    }

    /**
     * @inheritdoc
     */
    public function getStats(TaskLogFilter $filter)
    {
        $qb = $this->getQueryBuilder()
            ->from($this->getTableName());

        $qb->select(
            $this->buildCounterStatusSql(TasksLogsStats::IN_PROGRESS_TASKS, CategorizedStatus::getMappedStatuses(CategorizedStatus::STATUS_IN_PROGRESS)) . ', ' .
            $this->buildCounterStatusSql(TasksLogsStats::COMPLETED_TASKS, CategorizedStatus::getMappedStatuses(CategorizedStatus::STATUS_COMPLETED)) . ', ' .
            $this->buildCounterStatusSql(TasksLogsStats::FAILED_TASKS, CategorizedStatus::getMappedStatuses(CategorizedStatus::STATUS_FAILED))
        );

        $filter->applyFilters($qb);

        $row = $qb->execute()->fetch();

        return TasksLogsStats::buildFromArray($row);
    }

    /**
     * @inheritdoc
     */
    public function archive(EntityInterface $entity)
    {
        return $this->updateStatus($entity->getId(), TaskLogInterface::STATUS_ARCHIVED);
    }

    /**
     * @inheritdoc
     */
    public function cancel(EntityInterface $entity)
    {
        return $this->updateStatus($entity->getId(), TaskLogInterface::STATUS_CANCELLED);
    }

    /**
     * @inheritdoc
     */
    public function archiveCollection(CollectionInterface $collection)
    {
        return $this->updateCollectionStatus($collection, TaskLogInterface::STATUS_ARCHIVED);
    }

    /**
     * @inheritdoc
     */
    public function cancelCollection(CollectionInterface $collection)
    {
        return $this->updateCollectionStatus($collection, TaskLogInterface::STATUS_CANCELLED);
    }

    /**
     * @inheritdoc
     */
    public function deleteById($taskId)
    {
        $this->getPersistence()->getPlatform()->beginTransaction();

        try {
            $qb = $this->getQueryBuilder()
                ->delete($this->getTableName())
                ->where(self::COLUMN_ID .' = :id')
                ->setParameter('id', (string) $taskId);

            $qb->execute();
            $this->getPersistence()->getPlatform()->commit();

        } catch (\Exception $e) {
            $this->getPersistence()->getPlatform()->rollBack();

            return false;
        }

        return true;
    }

    /**
     * @return QueryBuilder
     */
    private function getQueryBuilder()
    {
        /**@var \common_persistence_sql_pdo_mysql_Driver $driver */
        return $this->getPersistence()->getPlatform()->getQueryBuilder();
    }

    /**
     * @param string $statusColumn
     * @param array $inStatuses
     * @return string
     */
    private function buildCounterStatusSql($statusColumn, array $inStatuses)
    {
        if (empty($inStatuses)) {
            return '';
        }

        $sql =  "COUNT( CASE WHEN ";
        foreach ($inStatuses as $status)
        {
            if ($status !== reset($inStatuses)) {
                $sql .= " OR ". self::COLUMN_STATUS ." = '". $status ."'";
            } else {
                $sql .= " ". self::COLUMN_STATUS ." = '". $status."'";
            }
        }

        $sql .= " THEN 0 END ) AS $statusColumn";

        return $sql;
    }

    /**
     * @param CollectionInterface $collection
     * @param string $status
     * @return int Number of rows updated
     */
    private function updateCollectionStatus(CollectionInterface $collection, $status)
    {
        $this->getPersistence()->getPlatform()->beginTransaction();

        try {
            $qb = $this->getQueryBuilder()
                ->update($this->getTableName())
                ->set(self::COLUMN_STATUS, ':status_new')
                ->set(self::COLUMN_UPDATED_AT, ':updated_at')
                ->where(self::COLUMN_ID .' IN(:id)')
                ->setParameter('id', $collection->getIds(), Connection::PARAM_STR_ARRAY)
                ->setParameter('status_new', (string) $status)
                ->setParameter('updated_at', $this->getPersistence()->getPlatForm()->getNowExpression());

            $exec = $qb->execute();
            $this->getPersistence()->getPlatform()->commit();

        } catch (\Exception $e) {
            $this->getPersistence()->getPlatform()->rollBack();
            $this->logDebug($e->getMessage());

            return false;
        }

        return $exec;
    }
}
