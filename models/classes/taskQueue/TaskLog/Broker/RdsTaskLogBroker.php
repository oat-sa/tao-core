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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\taskQueue\TaskLog\Broker;

use common_persistence_sql_SchemaManager;
use common_persistence_SqlPersistence as SqlPersistence;
use common_persistence_Persistence as Persistence;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use common_report_Report as Report;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLog\CategorizedStatus;
use oat\tao\model\taskQueue\TaskLog\CollectionInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLog\TasksLogsStats;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\oatbox\log\LoggerAwareTrait;

/**
 * Storing message logs in RDS.
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class RdsTaskLogBroker extends AbstractTaskLogBroker
{
    use LoggerAwareTrait;

    /** @var string */
    private $persistenceId;

    /** @var SqlPersistence */
    protected $persistence;

    /**
     * RdsTaskLogBroker constructor.
     *
     * @param string $persistenceId
     * @param null $containerName
     */
    public function __construct(string $persistenceId, string $containerName = null)
    {
        if (empty($persistenceId)) {
            throw new \InvalidArgumentException("Persistence id needs to be set for " . __CLASS__);
        }

        $this->persistenceId = $persistenceId;
        $this->containerName = $containerName === null ? self::DEFAULT_CONTAINER_NAME: $containerName;
    }

    /**
     * @inheritdoc
     */
    public function createContainer(): void
    {
        /** @var common_persistence_sql_SchemaManager $schemaManager */
        $schemaManager = $this->getPersistence()->getSchemaManager();

        $fromSchema = $schemaManager->createSchema();
        $toSchema = clone $fromSchema;

        // if our table does not exist, let's create it
        if (false === $fromSchema->hasTable($this->getTableName())) {
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
            $table->addIndex([self::COLUMN_TASK_NAME, self::COLUMN_OWNER], $this->getTableName() . 'IDX_task_name_owner');
            $table->addIndex([self::COLUMN_STATUS], $this->getTableName() . 'IDX_status');
            $table->addIndex([self::COLUMN_CREATED_AT], $this->getTableName() . 'IDX_created_at');

            $queries = $this->getPersistence()->getPlatForm()->getMigrateSchemaSql($fromSchema, $toSchema);
            foreach ($queries as $query) {
                $this->getPersistence()->exec($query);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function add(TaskInterface $task, string $status, string $label = null): void
    {
        $this->getPersistence()->insert($this->getTableName(), [
            self::COLUMN_ID   => (string) $task->getId(),
            self::COLUMN_PARENT_ID  => $task->getParentId() ? (string) $task->getParentId() : null,
            self::COLUMN_TASK_NAME => $task instanceof CallbackTaskInterface && is_object($task->getCallable()) ? get_class($task->getCallable()) : get_class($task),
            self::COLUMN_PARAMETERS => json_encode($task->getParameters()),
            self::COLUMN_LABEL => (string) $label,
            self::COLUMN_STATUS => $status,
            self::COLUMN_OWNER => (string) $task->getOwner(),
            self::COLUMN_CREATED_AT => $task->getCreatedAt()->format($this->getPersistence()->getPlatForm()->getDateTimeFormatString()),
            self::COLUMN_UPDATED_AT => $this->getPersistence()->getPlatForm()->getNowExpression(),
            self::COLUMN_MASTER_STATUS => $task->isMasterStatus(),
        ], $this->getTypes());
    }

    protected function getTypes(array $data = []): array
    {
        return [
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::BOOLEAN,
        ];
    }

    public function __toPhpCode()
    {
        return 'new ' . get_called_class() . '('
            . \common_Utils::toHumanReadablePhpString($this->persistenceId)
            . ', '
            . \common_Utils::toHumanReadablePhpString($this->containerName)
            . ')';
    }

    /**
     * @inheritdoc
     */
    public function updateStatus(string $taskId, string $newStatus, string $prevStatus = null): int
    {
        $qb = $this->getQueryBuilder()
            ->update($this->getTableName())
            ->set(self::COLUMN_STATUS, ':status_new')
            ->set(self::COLUMN_UPDATED_AT, ':updated_at')
            ->where(self::COLUMN_ID . ' = :id')
            ->setParameter('id', (string) $taskId)
            ->setParameter('status_new', (string) $newStatus)
            ->setParameter('updated_at', $this->getPersistence()->getPlatForm()->getNowExpression());

        if ($prevStatus) {
            $qb->andWhere(self::COLUMN_STATUS . ' = :status_prev')
                ->setParameter('status_prev', (string) $prevStatus);
        }

        return $qb->execute();
    }

    /**
     * @inheritdoc
     */
    public function addReport(string $taskId, Report $report, string $newStatus = null): int
    {
        $qb = $this->getQueryBuilder()
            ->update($this->getTableName())
            ->set(self::COLUMN_REPORT, ':report')
            ->set(self::COLUMN_STATUS, ':status_new')
            ->set(self::COLUMN_UPDATED_AT, ':updated_at')
            ->andWhere(self::COLUMN_ID . ' = :id')
            ->setParameter('id', (string) $taskId)
            ->setParameter('report', json_encode($report))
            ->setParameter('status_new', (string) $newStatus)
            ->setParameter('updated_at', $this->getPersistence()->getPlatForm()->getNowExpression());

        return $qb->execute();
    }

    /**
     * @inheritdoc
     */
    public function getStats(TaskLogFilter $filter): TasksLogsStats
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
    public function archive(EntityInterface $entity): bool
    {
        return $this->updateStatus($entity->getId(), TaskLogInterface::STATUS_ARCHIVED);
    }

    /**
     * @inheritdoc
     */
    public function cancel(EntityInterface $entity): bool
    {
        return $this->updateStatus($entity->getId(), TaskLogInterface::STATUS_CANCELLED);
    }

    /**
     * @inheritdoc
     */
    public function archiveCollection(CollectionInterface $collection): int
    {
        return $this->updateCollectionStatus($collection, TaskLogInterface::STATUS_ARCHIVED);
    }

    /**
     * @inheritdoc
     */
    public function cancelCollection(CollectionInterface $collection): int
    {
        return $this->updateCollectionStatus($collection, TaskLogInterface::STATUS_CANCELLED);
    }

    /**
     * @inheritdoc
     */
    public function deleteById($taskId): bool
    {
        $this->getPersistence()->getPlatform()->beginTransaction();

        try {
            $qb = $this->getQueryBuilder()
                ->delete($this->getTableName())
                ->where(self::COLUMN_ID . ' = :id')
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
        foreach ($inStatuses as $status) {
            if ($status !== reset($inStatuses)) {
                $sql .= " OR " . self::COLUMN_STATUS . " = '" . $status . "'";
            } else {
                $sql .= " " . self::COLUMN_STATUS . " = '" . $status . "'";
            }
        }

        $sql .= " THEN 0 END ) AS $statusColumn";

        return $sql;
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return strtolower(QueueDispatcherInterface::QUEUE_PREFIX . '_' . $this->containerName);
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
                ->where(self::COLUMN_ID . ' IN(:id)')
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

    protected function getPersistence(): Persistence
    {
        if ($this->persistence === null) {
            $this->persistence = $this->getServiceLocator()
                ->get(PersistenceManager::SERVICE_ID)
                ->getPersistenceById($this->persistenceId);
        }

        return $this->persistence;
    }


    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->getPersistence()->getPlatform()->getQueryBuilder();
    }
}
