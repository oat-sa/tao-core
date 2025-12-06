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

namespace oat\tao\test\unit\model\taskQueue\TaskLog;

use oat\tao\model\taskQueue\TaskLog\TasksLogsStats;
use PHPUnit\Framework\TestCase;

class TasksLogsStatsTest extends TestCase
{
    public function testCreateStats()
    {
        $stats = new TasksLogsStats(10, 2, 5);

        $this->assertSame(10, $stats->getNumberOfTasksCompleted());
        $this->assertSame(2, $stats->getNumberOfTasksFailed());
        $this->assertSame(5, $stats->getNumberOfTasksInProgress());

        $this->assertEquals([
            'numberOfTasksCompleted' => 10,
            'numberOfTasksFailed' => 2,
            'numberOfTasksInProgress' => 5,
        ], $stats->jsonSerialize());
    }
}
