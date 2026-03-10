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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\taskQueue\TaskLog\Broker;

use common_exception_Error;
use common_persistence_Persistence as Persistence;
use common_report_Report as Report;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\PhpSerializable;
use oat\oatbox\reporting\Report as NewReport;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLog\CollectionInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogCollection;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLog\TasksLogsStats;
use Psr\Log\LoggerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

abstract class AbstractTaskLogBroker implements
    TaskLogBrokerInterface,
    PhpSerializable,
    LoggerAwareInterface,
    RdsTaskLogBrokerInterface
{
    use ServiceLocatorAwareTrait;
    use LoggerAwareTrait;


    /** @var string|null */
    protected $containerName;

    /** @var Persistence */
    private $persistence;

    /**
     * @inheritdoc
     */
    public function getStatus(string $taskId): string
    {
        $qb = $this->getQueryBuilder()
            ->select(self::COLUMN_STATUS)
            ->from($this->getTableName())
            ->andWhere(self::COLUMN_ID . ' = :id')
            ->setParameter('id', $taskId);

        return (string)$qb->executeQuery()->fetchOne();
    }

    /**
     * @inheritdoc
     */
    abstract public function getTableName(): string;

    /**
     * @inheritdoc
     */
    abstract public function add(TaskInterface $task, string $status, string $label = null): void;

    /**
     * @inheritdoc
     */
    abstract public function updateStatus(string $taskId, string $newStatus, string $prevStatus = null): int;

    /**
     * @inheritdoc
     */
    abstract public function addReport(string $taskId, Report $report, string $newStatus = null): int;

    /**
     * @inheritdoc
     * @throws common_exception_Error
     */
    public function getReport(string $taskId): ?Report
    {
        $qb = $this->getQueryBuilder()
            ->select(self::COLUMN_REPORT)
            ->from($this->getTableName())
            ->andWhere(self::COLUMN_ID . ' = :id')
            ->setParameter('id', $taskId);

        if (
            ($reportJson = $qb->executeQuery()->fetchOne())
            && ($reportData = json_decode($reportJson, true)) !== null
            && json_last_error() === JSON_ERROR_NONE
        ) {
            // if we have a valid JSON string and no JSON error, let's restore the report object
            return NewReport::jsonUnserialize($reportData);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function search(TaskLogFilter $filter): iterable
    {
        try {
            $qb = $this->getSearchQuery($filter);
            $collection = TaskLogCollection::createFromArray(
                $qb->executeQuery()->fetchAllAssociative(),
                $this->getPersistence()->getPlatForm()->getDateTimeFormatString()
            );
        } catch (Exception $exception) {
            $this->logError('Searching for task logs failed with MSG: ' . $exception->getMessage());
            $collection = TaskLogCollection::createEmptyCollection();
        }

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function count(TaskLogFilter $filter): int
    {
        try {
            return (int)$this->getCountQuery($filter)
                ->executeQuery()
                ->fetchOne();
        } catch (Exception $e) {
            $this->logError('Counting task logs failed with MSG: ' . $e->getMessage());
        }

        return 0;
    }

    private function getSearchQuery(TaskLogFilter $filter): QueryBuilder
    {
        $qb = $this->getQueryBuilder()
            ->select($filter->getColumns())
            ->from($this->getTableName())
            ->setFirstResult($filter->getOffset() ?? 0);

        $limit = $filter->getLimit();
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($filter->getSortBy()) {
            $qb->orderBy($filter->getSortBy(), $filter->getSortOrder());
        } else {
            $qb->orderBy(TaskLogBrokerInterface::COLUMN_CREATED_AT, 'DESC');
        }

        $filter->applyFilters($qb);
        return $qb;
    }

    private function getCountQuery(TaskLogFilter $filter): QueryBuilder
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(*)')
            ->from($this->getTableName());

        $filter->applyFilters($qb);
        return $qb;
    }

    abstract protected function getQueryBuilder(): QueryBuilder;

    abstract protected function getPersistence(): Persistence;

    abstract public function getStats(TaskLogFilter $filter): TasksLogsStats;

    /**
     * @inheritdoc
     */
    abstract public function archive(EntityInterface $entity): bool;

    /**
     * @inheritdoc
     */
    abstract public function cancel(EntityInterface $entity): bool;

    /**
     * @inheritdoc
     */
    abstract public function archiveCollection(CollectionInterface $collection): int;

    /**
     * @inheritdoc
     */
    abstract public function cancelCollection(CollectionInterface $collection): int;

    /**
     * @inheritdoc
     */
    abstract public function deleteById(string $taskId): bool;
}
