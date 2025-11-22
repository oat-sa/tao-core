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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\test\unit\model\taskQueue\Task;

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\action\ActionService;
use oat\tao\model\taskQueue\Task\CallbackTask;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\Task\TaskSerializerService;

class TaskSerializerServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    public function testDeserialize()
    {
        $service = new TaskSerializerService();

        $service->setServiceLocator(
            $this->getServiceManagerMock([
                ActionService::SERVICE_ID => $this->mockActionService()
            ])
        );
        $taskJson = '{"taskFqcn":"oat\\\\tao\\\\model\\\\taskQueue\\\\Task\\\\CallbackTask","metadata":{"__id__":'
            . '"http://www.taotesting.com/ontologies/tao.rdf#i15402228521886108","__created_at__":'
            . '"2018-10-22T15:40:52+00:00","__owner__":"","__master_status__":0,"__label__":"ExportByHandler",'
            . '"__callable__":"oat\\\\tao\\\\model\\\\task\\\\ExportByHandler"},"parameters":[]}';

        $this->assertInstanceOf(TaskInterface::class, $service->deserialize($taskJson, []));
        $this->assertInstanceOf(CallbackTask::class, $service->deserialize($taskJson, []));
    }

    public function testSerialize()
    {
        $service = new TaskSerializerService();

        $task = $this->createMock(TaskInterface::class);
        $this->assertIsString($service->serialize($task));
    }

    protected function mockActionService()
    {
        $action = $this->getMockBuilder(ActionService::class)->disableOriginalConstructor()->getMock();

        $action
            ->method('resolve')
            ->willReturn(function () {
            });

        return $action;
    }
}
