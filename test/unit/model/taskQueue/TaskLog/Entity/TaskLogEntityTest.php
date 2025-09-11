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
 * Copyright (c) 2017-2025 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\taskQueue\TaskLog\Entity;

use common_report_Report as Report;
use DateTime;
use DateTimeZone;
use oat\tao\model\taskQueue\TaskLog\CategorizedStatus;
use oat\tao\model\taskQueue\TaskLog\Entity\TaskLogEntity;
use oat\tao\model\taskQueue\TaskLogInterface;
use PHPUnit\Framework\TestCase;

class TaskLogEntityTest extends TestCase
{
    public function testEntityCreated()
    {
        $createdAt = new DateTime('2017-11-16 14:11:42', new DateTimeZone('UTC'));
        $updatedAt = new DateTime('2017-11-16 17:12:30', new DateTimeZone('UTC'));

        $entity = TaskLogEntity::createFromArray([
            'id' => 'rdf#i1508337970199318643',
            'parent_id' => 'parentFake0002525',
            'task_name' => 'Task Name',
            'parameters' => json_encode(['param1' => 'value1', 'param2' => 'value2']),
            'label' => 'Task label',
            'status' => TaskLogInterface::STATUS_COMPLETED,
            'owner' => 'userId',
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $updatedAt->format('Y-m-d H:i:s'),
            'report' => [
                'type' => 'info',
                'message' => 'Running task http://www.taoinstance.dev/ontologies/tao.rdf#i1508337970199318643',
                'data' => null,
                'children' => []
            ],
            'master_status' => true
        ], DateTime::RFC3339);

        $this->assertInstanceOf(TaskLogEntity::class, $entity);
        $this->assertInstanceOf(CategorizedStatus::class, $entity->getStatus());
        $this->assertInstanceOf(Report::class, $entity->getReport());
        $this->assertIsString($entity->getId());
        $this->assertIsString($entity->getTaskName());
        $this->assertIsArray($entity->getParameters());
        $this->assertIsString($entity->getLabel());
        $this->assertIsString($entity->getOwner());

        $this->assertEquals([
            'id' => 'rdf#i1508337970199318643',
            'taskName' => 'Task Name',
            'taskLabel' => 'Task label',
            'status' => 'completed',
            'statusLabel' => 'Completed',
            'report' => [
                'type' => 'info',
                'message' => 'Running task http://www.taoinstance.dev/ontologies/tao.rdf#i1508337970199318643',
                'data' => null,
                'children' => []
            ],
            'masterStatus' => true
        ], $entity->jsonSerialize());
    }

    public function testCreateWithReportNull()
    {
        $entity = TaskLogEntity::createFromArray([
            'id' => 'rdf#i1508337970199318643',
            'parent_id' => 'parentFake0002525',
            'task_name' => 'Task Name',
            'parameters' => json_encode(['param1' => 'value1', 'param2' => 'value2']),
            'label' => 'Task label',
            'status' => TaskLogInterface::STATUS_COMPLETED,
            'owner' => 'userId',
            'created_at' => '2017-02-01 12:00:01',
            'updated_at' => '2017-02-01 14:00:01',
            'report' => [],
        ], DateTime::RFC3339);

        $this->assertNull($entity->getReport());
    }
}
