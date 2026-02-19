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
 * Copyright (c) 2017-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\taskQueue\TaskLog;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use InvalidArgumentException;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLogInterface;

class TaskLogFilter
{
    private const OP_EQ  = '=';
    private const OP_NEQ = '!=';
    private const OP_LT  = '<';
    private const OP_LTE = '<=';
    private const OP_GT  = '>';
    private const OP_GTE = '>=';
    private const OP_LIKE = 'LIKE';
    private const OP_NOT_LIKE = 'NOT LIKE';
    private const OP_IN = 'IN';
    private const OP_NOT_IN = 'NOT IN';

    private $filters = [];
    private $limit;
    private $offset;
    private $sortBy;
    private $sortOrder;

    private $baseColumns = [
        TaskLogBrokerInterface::COLUMN_ID,
        TaskLogBrokerInterface::COLUMN_PARENT_ID,
        TaskLogBrokerInterface::COLUMN_TASK_NAME,
        TaskLogBrokerInterface::COLUMN_STATUS,
        TaskLogBrokerInterface::COLUMN_MASTER_STATUS,
        TaskLogBrokerInterface::COLUMN_REPORT
    ];

    private $optionalColumns = [
        TaskLogBrokerInterface::COLUMN_PARAMETERS,
        TaskLogBrokerInterface::COLUMN_LABEL,
        TaskLogBrokerInterface::COLUMN_OWNER,
        TaskLogBrokerInterface::COLUMN_CREATED_AT,
        TaskLogBrokerInterface::COLUMN_UPDATED_AT
    ];

    private $deselectedColumns = [];

    private $ignoredTasks = [];

    public function getColumns(): array
    {
        return array_merge($this->baseColumns, array_diff($this->optionalColumns, $this->deselectedColumns));
    }

    public function deselect(string $column): self
    {
        if (!in_array($column, $this->optionalColumns)) {
            throw new InvalidArgumentException('Column "' . $column . '"" is not valid column or not unselectable.');
        }

        $this->deselectedColumns[] = $column;

        return $this;
    }

    public function getSortBy()
    {
        return $this->sortBy;
    }

    public function setSortBy($sortBy): self
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function setSortOrder($sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = max(0, $limit);

        return $this;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = max(0, $offset);

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function addFilter(string $field, string $operator, $value): self
    {
        $this->assertValidOperator($operator);

        $this->filters[] =  [
            'column' => $field,
            'columnSqlTranslate' => ':' . $field . uniqid(), // we need a unique placeholder
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    /**
     * Add a basic filter to query only rows belonging to a given user and not having status ARCHIVED or CANCELLED.
     */
    public function addAvailableFilters(
        string $userId,
        bool $archivedAllowed = false,
        bool $cancelledAvailable = false
    ): self {
        if (!$archivedAllowed) {
            $this->neq(TaskLogBrokerInterface::COLUMN_STATUS, TaskLogInterface::STATUS_ARCHIVED);
        }

        if (!$cancelledAvailable) {
            $this->neq(TaskLogBrokerInterface::COLUMN_STATUS, TaskLogInterface::STATUS_CANCELLED);
        }

        if ($userId !== TaskLogInterface::SUPER_USER) {
            $this->eq(TaskLogBrokerInterface::COLUMN_OWNER, $userId);
        }

        if ($this->ignoredTasks) {
            $this->notIn(TaskLogBrokerInterface::COLUMN_TASK_NAME, $this->ignoredTasks);
        }

        return $this;
    }

    public function availableForArchived(string $userId): self
    {
        $this->in(
            TaskLogBrokerInterface::COLUMN_STATUS,
            [TaskLogInterface::STATUS_FAILED, TaskLogInterface::STATUS_COMPLETED]
        );

        if ($userId !== TaskLogInterface::SUPER_USER) {
            $this->eq(TaskLogBrokerInterface::COLUMN_OWNER, $userId);
        }

        return $this;
    }

    public function availableForCancelled(string $userId): self
    {
        $this->eq(TaskLogBrokerInterface::COLUMN_STATUS, TaskLogInterface::STATUS_ENQUEUED);

        if ($userId !== TaskLogInterface::SUPER_USER) {
            $this->eq(TaskLogBrokerInterface::COLUMN_OWNER, $userId);
        }

        return $this;
    }

    public function applyFilters(QueryBuilder $qb): QueryBuilder
    {
        foreach ($this->getFilters() as $filter) {
            $withParentheses = is_array($filter['value']) ? true : false;
            $type = is_array($filter['value']) ? Connection::PARAM_STR_ARRAY : null;
            $paramName = ltrim($filter['columnSqlTranslate'], ':');

            $qb
                ->andWhere(
                    $filter['column'] . ' ' . $filter['operator'] . ' ' . ($withParentheses ? '(' : '')
                        . $filter['columnSqlTranslate'] . ($withParentheses ? ')' : '')
                )
                ->setParameter($paramName, $filter['value'], $type);
        }

        return $qb;
    }

    public function eq(string $field, $value): self
    {
        return $this->addFilter($field, self::OP_EQ, $value);
    }

    public function neq(string $field, $value): self
    {
        return $this->addFilter($field, self::OP_NEQ, $value);
    }

    public function lt(string $field, $value): self
    {
        return $this->addFilter($field, self::OP_LT, $value);
    }

    public function lte(string $field, $value): self
    {
        return $this->addFilter($field, self::OP_LTE, $value);
    }

    public function gt(string $field, $value): self
    {
        return $this->addFilter($field, self::OP_GT, $value);
    }

    public function gte(string $field, $value): self
    {
        return $this->addFilter($field, self::OP_GTE, $value);
    }

    public function like(string $field, $value): self
    {
        return $this->addFilter($field, self::OP_LIKE, $value);
    }

    public function notLike(string $field, string $value): self
    {
        return $this->addFilter($field, self::OP_NOT_LIKE, $value);
    }

    public function in(string $field, array $value): self
    {
        return $this->addFilter($field, self::OP_IN, $value);
    }

    public function notIn(string $field, array $value): self
    {
        return $this->addFilter($field, self::OP_NOT_IN, $value);
    }

    public function withIgnoredTasks(array $ignoredTasks): self
    {
        $this->ignoredTasks = $ignoredTasks;
        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertValidOperator($op): void
    {
        $operators = [
            self::OP_EQ,
            self::OP_NEQ,
            self::OP_LT,
            self::OP_LTE,
            self::OP_GT,
            self::OP_GTE,
            self::OP_LIKE,
            self::OP_NOT_LIKE,
            self::OP_IN,
            self::OP_NOT_IN,
        ];

        if (!in_array($op, $operators)) {
            throw new InvalidArgumentException('Operator "' . $op . '"" is not a valid operator.');
        }
    }
}
