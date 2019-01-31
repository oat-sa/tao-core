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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLogInterface;

class TaskLogFilter
{
    const OP_EQ  = '=';
    const OP_NEQ = '!=';
    const OP_LT  = '<';
    const OP_LTE = '<=';
    const OP_GT  = '>';
    const OP_GTE = '>=';
    const OP_LIKE = 'LIKE';
    const OP_NOT_LIKE = 'NOT LIKE';
    const OP_IN = 'IN';
    const OP_NOT_IN = 'NOT IN';

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

    /**
     * @return array
     */
    public function getColumns()
    {
        return array_merge($this->baseColumns, array_diff($this->optionalColumns, $this->deselectedColumns));
    }

    /**
     * @param string $column
     * @return $this
     */
    public function deselect($column)
    {
        if (!in_array($column, $this->optionalColumns)) {
            throw new \InvalidArgumentException('Column "'. $column .'"" is not valid column or not unselectable.');
        }

        $this->deselectedColumns[] = $column;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param mixed $sortBy
     * @return TaskLogFilter
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param mixed $sortOrder
     * @return TaskLogFilter
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return TaskLogFilter
     */
    public function setLimit($limit)
    {
        $this->limit = max(0, $limit);

        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return TaskLogFilter
     */
    public function setOffset($offset)
    {
        $this->offset = max(0, $offset);

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return TaskLogFilter
     */
    public function addFilter($field, $operator, $value)
    {
        $this->assertValidOperator($operator);

        $this->filters[] =  [
            'column' => (string) $field,
            'columnSqlTranslate' => ':'. $field . uniqid(), // we need a unique placeholder
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    /**
     * Add a basic filter to query only rows belonging to a given user and not having status ARCHIVED or CANCELLED.
     *
     * @param string $userId
     * @param bool $archivedAllowed
     * @param bool $cancelledAvailable
     * @return $this
     */
    public function addAvailableFilters($userId, $archivedAllowed = false, $cancelledAvailable = false)
    {
        if (!$archivedAllowed) {
            $this->neq(TaskLogBrokerInterface::COLUMN_STATUS, TaskLogInterface::STATUS_ARCHIVED);
        }

        if (!$cancelledAvailable) {
            $this->neq(TaskLogBrokerInterface::COLUMN_STATUS, TaskLogInterface::STATUS_CANCELLED);
        }

        if ($userId !== TaskLogInterface::SUPER_USER) {
            $this->eq(TaskLogBrokerInterface::COLUMN_OWNER, $userId);
        }

        return $this;
    }

    /**
     * @param $userId
     * @return $this
     */
    public function availableForArchived($userId)
    {
        $this->in(TaskLogBrokerInterface::COLUMN_STATUS, [TaskLogInterface::STATUS_FAILED, TaskLogInterface::STATUS_COMPLETED]);

        if ($userId !== TaskLogInterface::SUPER_USER) {
            $this->eq(TaskLogBrokerInterface::COLUMN_OWNER, $userId);
        }

        return $this;
    }

    /**
     * @param $userId
     * @return $this
     */
    public function availableForCancelled($userId)
    {
        $this->eq(TaskLogBrokerInterface::COLUMN_STATUS, TaskLogInterface::STATUS_ENQUEUED);

        if ($userId !== TaskLogInterface::SUPER_USER) {
            $this->eq(TaskLogBrokerInterface::COLUMN_OWNER, $userId);
        }

        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    public function applyFilters(QueryBuilder $qb)
    {
        foreach ($this->getFilters() as $filter) {
            $withParentheses = is_array($filter['value']) ? true : false;
            $type = is_array($filter['value']) ? Connection::PARAM_STR_ARRAY : null;

            $qb->andWhere($filter['column'] .' '. $filter['operator'] .' '. ($withParentheses ? '(' : '') . $filter['columnSqlTranslate'] . ($withParentheses ? ')' : ''))
                ->setParameter($filter['columnSqlTranslate'], $filter['value'], $type);
        }

        return $qb;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return TaskLogFilter
     */
    public function eq($field, $value)
    {
        return $this->addFilter($field, self::OP_EQ, $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return TaskLogFilter
     */
    public function neq($field, $value)
    {
        return $this->addFilter($field, self::OP_NEQ, $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return TaskLogFilter
     */
    public function lt($field, $value)
    {
        return $this->addFilter($field, self::OP_LT, $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return TaskLogFilter
     */
    public function lte($field, $value)
    {
        return $this->addFilter($field, self::OP_LTE, $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return TaskLogFilter
     */
    public function gt($field, $value)
    {
        return $this->addFilter($field, self::OP_GT, $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return TaskLogFilter
     */
    public function gte($field, $value)
    {
        return $this->addFilter($field, self::OP_GTE, $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return TaskLogFilter
     */
    public function like($field, $value)
    {
        return $this->addFilter($field, self::OP_LIKE, $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return TaskLogFilter
     */
    public function notLike($field, $value)
    {
        return $this->addFilter($field, self::OP_NOT_LIKE, $value);
    }

    /**
     * @param string $field
     * @param array $value
     * @return TaskLogFilter
     */
    public function in($field, array $value)
    {
        return $this->addFilter($field, self::OP_IN, $value);
    }

    /**
     * @param string $field
     * @param array $value
     * @return TaskLogFilter
     */
    public function notIn($field, array $value)
    {
        return $this->addFilter($field, self::OP_NOT_IN, $value);
    }

    /**
     * @param $op
     * @throws \InvalidArgumentException
     */
    private function assertValidOperator($op)
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
            throw new \InvalidArgumentException('Operator "'. $op .'"" is not a valid operator.');
        }
    }
}
