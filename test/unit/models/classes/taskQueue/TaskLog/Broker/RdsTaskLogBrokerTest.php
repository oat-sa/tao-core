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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\taoSystemStatus\test\model\Check\System;

use common_persistence_Persistence;
use DateTime;
use oat\generis\test\PersistenceManagerMockTrait;
use oat\tao\model\taskQueue\TaskLog\Broker\RdsTaskLogBroker;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use PHPUnit\Framework\TestCase;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use ReflectionProperty;

class RdsTaskLogBrokerTest extends TestCase
{
    use PersistenceManagerMockTrait;

    private RdsTaskLogBroker $subject;

    protected function setUp(): void
    {
        $this->subject = new RdsTaskLogBroker('not-used');
    }

    public function testGetTaskExecutionTimesByDateRange(): void
    {
        $persistence = $this->getPersistenceManagerMock('fixture')->getPersistenceById('fixture');
        $this->createTqTaskLogTable($persistence);

        $tasks = [
            [
                $fixtureId = 123,
                'test',
                'completed',
                '2022-12-02 08:15:28',
                '2022-12-02 08:15:58'
            ],
            [
                $fixtureId2 = 456,
                'test2',
                'archived',
                '2022-12-02 11:15:28',
                '2022-12-02 11:17:01'
            ],
            [
                //Should not happen but update_at is nullable
                $fixtureId3 = 789,
                'test3',
                'archived',
                '2022-12-02 11:15:28',
                null
            ]
        ];

        $this->insertTask($persistence, $tasks);

        $from = new DateTime('2022-12-02 08:00:00');
        $to = new DateTime('2022-12-03 08:00:00');

        $result = $this->subject->getTaskExecutionTimesByDateRange($from, $to);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertArrayHasKey($fixtureId, $result);
        $this->assertEquals(30, $result[$fixtureId]);
        $this->assertArrayHasKey($fixtureId2, $result);
        $this->assertEquals(93, $result[$fixtureId2]);
    }

    private function createTqTaskLogTable(common_persistence_Persistence $persistence): void
    {
        $property = new ReflectionProperty(RdsTaskLogBroker::class, 'persistence');
        $property->setAccessible(true);
        $property->setValue($this->subject, $persistence);

        $this->subject->createContainer();
    }

    private function insertTask(common_persistence_Persistence $persistence, array $tasks): void
    {
        $parameterPlaceholders = rtrim(str_repeat("(?, ?, ?, ?, ?),", count($tasks)), ',');
        $query = sprintf(
            "insert into tq_task_log (%s,%s,%s,%s,%s) values %s",
            TaskLogBrokerInterface::COLUMN_ID,
            TaskLogBrokerInterface::COLUMN_TASK_NAME,
            TaskLogBrokerInterface::COLUMN_STATUS,
            TaskLogBrokerInterface::COLUMN_CREATED_AT,
            TaskLogBrokerInterface::COLUMN_UPDATED_AT,
            $parameterPlaceholders
        );

        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($tasks));
        $parameters = [];
        foreach ($iterator as $it) {
            $parameters[] = $it;
        }

        $persistence->exec($query, $parameters);
    }
}
