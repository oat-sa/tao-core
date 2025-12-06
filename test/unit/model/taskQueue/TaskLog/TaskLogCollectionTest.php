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
 * Copyright (c) 2017-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\test\unit\model\taskQueue\TaskLog;

use DateTime;
use oat\tao\model\taskQueue\TaskLog\TaskLogCollection;
use oat\tao\model\taskQueue\TaskLogInterface;
use PHPUnit\Framework\TestCase;

class TaskLogCollectionTest extends TestCase
{
    /**
     * @throws \Exception
     * @throws \common_exception_Error
     */
    public function testCreateCollection()
    {
        $collection = $this->createCollection();

        $this->assertInstanceOf(TaskLogCollection::class, $collection);
    }

    /**
     * @throws \Exception
     * @throws \common_exception_Error
     */
    public function testCollectionToArray()
    {
        $collection = $this->createCollection();

        $this->assertisarray($collection->jsonSerialize());
    }

    /**
     * @return TaskLogCollection
     * @throws \Exception
     * @throws \common_exception_Error
     */
    protected function createCollection()
    {
        return TaskLogCollection::createFromArray([
            [
                'id' => 'rdf#i1508337970199318643',
                'parent_id' => 'parentFake0002525',
                'task_name' => 'Task Name',
                'label' => 'Task label',
                'status' => TaskLogInterface::STATUS_COMPLETED,
                'owner' => 'userId',
                'parameters' => json_encode([]),
                'created_at' => '2017-02-01 12:00:01',
                'updated_at' => '2017-02-01 14:00:01',
                'report' => [
                    'type' => 'info',
                    'message' => 'Running task rdf#i1508337970199318643',
                    'data' => null,
                    'children' => []
                ],
            ],
            [
                'id' => 'rdf#i15083379701993186432222',
                'parent_id' => 'parentFake0002525',
                'task_name' => 'Task Name 2',
                'label' => 'Task label  2',
                'status' => TaskLogInterface::STATUS_RUNNING,
                'owner' => 'userId',
                'parameters' => json_encode([]),
                'created_at' => '2017-02-01 16:00:01',
                'updated_at' => '2017-02-01 18:00:01',
                'report' => [
                    'type' => 'info',
                    'message' => 'Running task #i15083379701993186432222',
                    'data' => null,
                    'children' => []
                ],
            ],
            [
               'id' => 'rdf#i150833797019931864322223',
               'parent_id' => 'parentFake0002525',
               'task_name' => 'Task Name 3',
               'label' => 'Task label  3',
               'status' => TaskLogInterface::STATUS_RUNNING,
               'owner' => 'userId',
               'parameters' => json_encode([]),
               'created_at' => '2017-02-01 16:00:01',
               'updated_at' => '2017-02-01 18:00:01',
               'report' => [
                   'type' => 'info',
                   'message' => 'Running task #i15083379701993186433333',
                   'data' => null,
                   'children' => []
               ],
            ],
        ], DateTime::RFC3339);
    }
}
