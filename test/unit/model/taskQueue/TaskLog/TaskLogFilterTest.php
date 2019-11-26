<?php

declare(strict_types=1);

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
 */

namespace oat\tao\test\unit\model\taskQueue\TaskLog;

use oat\generis\test\TestCase;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLogInterface;

class TaskLogFilterTest extends TestCase
{
    /** @var TaskLogFilter */
    private $filter;

    protected function setUp(): void
    {
        $this->filter = new TaskLogFilter();
    }

    protected function tearDown(): void
    {
        $this->filter = null;
    }

    public function testAllColumnsShouldBeAvailableByDefault(): void
    {
        $this->assertSame([
            TaskLogBrokerInterface::COLUMN_ID,
            TaskLogBrokerInterface::COLUMN_PARENT_ID,
            TaskLogBrokerInterface::COLUMN_TASK_NAME,
            TaskLogBrokerInterface::COLUMN_STATUS,
            TaskLogBrokerInterface::COLUMN_MASTER_STATUS,
            TaskLogBrokerInterface::COLUMN_REPORT,
            TaskLogBrokerInterface::COLUMN_PARAMETERS,
            TaskLogBrokerInterface::COLUMN_LABEL,
            TaskLogBrokerInterface::COLUMN_OWNER,
            TaskLogBrokerInterface::COLUMN_CREATED_AT,
            TaskLogBrokerInterface::COLUMN_UPDATED_AT,
        ], $this->filter->getColumns());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDeselectingABaseColumnShouldThrowException(): void
    {
        $this->filter->deselect(TaskLogBrokerInterface::COLUMN_ID);
    }

    public function testDeselectedColumnShouldNotBeInTheFinalColumns(): void
    {
        $this->filter->deselect(TaskLogBrokerInterface::COLUMN_OWNER);

        $this->assertNotContains(TaskLogBrokerInterface::COLUMN_OWNER, $this->filter->getColumns());
    }

    public function testLimitOffsetSortFunctions(): void
    {
        $this->filter->setLimit(-10);
        $this->assertSame(0, $this->filter->getLimit(), 'Limit should be 0 if negative value is set.');

        $this->filter->setLimit(5);
        $this->assertSame(5, $this->filter->getLimit(), 'Limit should be 5');

        $this->filter->setOffset(-20);
        $this->assertSame(0, $this->filter->getOffset(), 'Offset should be 0 if negative value is set.');

        $this->filter->setOffset(55);
        $this->assertSame(55, $this->filter->getOffset(), 'Offset should be 55');

        $this->filter->setSortBy(TaskLogBrokerInterface::COLUMN_CREATED_AT);
        $this->assertSame(TaskLogBrokerInterface::COLUMN_CREATED_AT, $this->filter->getSortBy(), 'Sort by should be ' . TaskLogBrokerInterface::COLUMN_CREATED_AT);

        $this->filter->setSortOrder('DESC');
        $this->assertSame('DESC', $this->filter->getSortOrder(), 'Sort order should be DESC');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddFilterShouldThrowExceptionIfOperatorIsNotValid(): void
    {
        $this->filter->addFilter('fakeCol', 'fakeOp', 'fakeValue');
    }

    public function testAddAvailableFiltersWithStandardUser(): void
    {
        $this->filter->addAvailableFilters('standardUserId');

        $filters = $this->filter->getFilters();

        $this->assertCount(3, $filters);

        foreach ($filters as $filter) {
            $this->assertArrayHasKey('column', $filter);
            $this->assertArrayHasKey('columnSqlTranslate', $filter);
            $this->assertArrayHasKey('operator', $filter);
            $this->assertArrayHasKey('value', $filter);
        }
    }

    public function testAddAvailableFiltersWithSuperUser(): void
    {
        $this->filter->addAvailableFilters(TaskLogInterface::SUPER_USER);

        $filters = $this->filter->getFilters();

        $this->assertCount(2, $filters);

        foreach ($filters as $filter) {
            $this->assertArrayHasKey('column', $filter);
            $this->assertArrayHasKey('columnSqlTranslate', $filter);
            $this->assertArrayHasKey('operator', $filter);
            $this->assertArrayHasKey('value', $filter);
        }
    }

    public function testAddAvailableFiltersWithArchivedAllowed(): void
    {
        $this->filter->addAvailableFilters(TaskLogInterface::SUPER_USER, false, true);

        $filters = $this->filter->getFilters();

        $this->assertCount(1, $filters);

        foreach ($filters as $filter) {
            $this->assertArrayHasKey('column', $filter);
            $this->assertArrayHasKey('columnSqlTranslate', $filter);
            $this->assertArrayHasKey('operator', $filter);
            $this->assertArrayHasKey('value', $filter);
            $this->assertSame('archived', $filter['value']);
        }
    }

    public function testAddAvailableFiltersWithCancelledAllowed(): void
    {
        $this->filter->addAvailableFilters(TaskLogInterface::SUPER_USER, true, false);

        $filters = $this->filter->getFilters();

        $this->assertCount(1, $filters);

        foreach ($filters as $filter) {
            $this->assertArrayHasKey('column', $filter);
            $this->assertArrayHasKey('columnSqlTranslate', $filter);
            $this->assertArrayHasKey('operator', $filter);
            $this->assertArrayHasKey('value', $filter);
            $this->assertSame('cancelled', $filter['value']);
        }
    }

    public function testAddingFilters(): void
    {
        $this->filter->eq('field1', 'value1');

        $this->assertCount(1, $this->filter->getFilters());
        $this->assertSame('field1', $this->filter->getFilters()[0]['column']);
        $this->assertSame('value1', $this->filter->getFilters()[0]['value']);
    }
}
