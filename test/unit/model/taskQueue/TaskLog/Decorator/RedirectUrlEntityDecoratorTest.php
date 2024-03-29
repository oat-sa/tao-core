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

namespace oat\tao\test\unit\model\taskQueue\TaskLog\Decorator;

use DateTime;
use DateTimeZone;
use oat\oatbox\user\AnonymousUser;
use oat\tao\model\taskQueue\TaskLog;
use oat\tao\model\taskQueue\TaskLog\CategorizedStatus;
use oat\tao\model\taskQueue\TaskLog\Decorator\RedirectUrlEntityDecorator;
use oat\tao\model\taskQueue\TaskLog\Entity\TaskLogEntity;
use oat\tao\model\taskQueue\TaskLogInterface;
use Prophecy\Argument;
use oat\generis\test\TestCase;

class RedirectUrlEntityDecoratorTest extends TestCase
{
    private $createdAt;
    private $updatedAt;

    /**
     * @dataProvider taskLogStatusProvider
     * phpcs:disable PSR1.Methods.CamelCapsMethodName
     */
    public function testDecorator_NotUsed_excludedTaskLogStatus($taskStatus)
    {
        $entity = $this->getFixtureEntity();

        $taskLog = $this->prophesize(TaskLog::class);
        $taskLog->getCategoryForTask(Argument::is('Task Name'))->willReturn($taskStatus);

        $decorator = new RedirectUrlEntityDecorator($entity, $taskLog->reveal(), new AnonymousUser());

        $this->assertEquals($this->getFixtureEntityData(), $decorator->toArray());
    }
    // phpcs:enable PSR1.Methods.CamelCapsMethodName

    public function taskLogStatusProvider()
    {
        return [
            [TaskLogInterface::CATEGORY_DELETE],
            [TaskLogInterface::CATEGORY_EXPORT],
            [TaskLogInterface::CATEGORY_UNKNOWN],
            [TaskLogInterface::CATEGORY_UNRELATED_RESOURCE],
        ];
    }

    /**
     * @dataProvider entityTaskLogStatusProvider
     * phpcs:disable PSR1.Methods.CamelCapsMethodName
     */
    public function testDecorator_NotUsed_excludedTaskEntityStatus($status)
    {
        $entity = $this->getFixtureEntity($status);

        $taskLog = $this->prophesize(TaskLog::class);
        $taskLog->getCategoryForTask(Argument::is('Task Name'))->willReturn('notExcludedStatus');

        $redirectUrlEntityDecoratorMock = $this->getMockBuilder(RedirectUrlEntityDecorator::class)
            ->setConstructorArgs([$entity, $taskLog->reveal(), new AnonymousUser()])
            ->setMethods(['hasAccess'])
            ->getMock();
        $redirectUrlEntityDecoratorMock->expects($this->once())->method('hasAccess')->willReturn(false);

        $this->assertEquals($this->getFixtureEntityData($status), $redirectUrlEntityDecoratorMock->toArray());
    }
    // phpcs:enable PSR1.Methods.CamelCapsMethodName

    public function entityTaskLogStatusProvider()
    {
        return [
            [TaskLogInterface::STATUS_COMPLETED],
            [TaskLogInterface::STATUS_ARCHIVED],
        ];
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName
    public function testDecorator_Used()
    {
        $entity = $this->getFixtureEntity();

        $taskLog = $this->prophesize(TaskLog::class);
        $taskLog->getCategoryForTask(Argument::is('Task Name'))->willReturn('notExcludedStatus');

        $redirectUrlEntityDecoratorMock = $this->getMockBuilder(RedirectUrlEntityDecorator::class)
            ->setConstructorArgs([$entity, $taskLog->reveal(), new AnonymousUser()])
            ->setMethods(['hasAccess'])
            ->getMock();
        $redirectUrlEntityDecoratorMock->expects($this->once())->method('hasAccess')->willReturn(true);

        $expectedData = array_merge(
            $this->getFixtureEntityData(),
            [
                'redirectUrl' => _url(
                    'redirectTaskToInstance',
                    'Redirector',
                    'taoBackOffice',
                    ['taskId' => $entity->getId()]
                )
            ]
        );

        $this->assertEquals($expectedData, $redirectUrlEntityDecoratorMock->toArray());
    }
    // phpcs:enable PSR1.Methods.CamelCapsMethodName

    protected function getFixtureEntity($status = TaskLogInterface::STATUS_COMPLETED)
    {
        $this->createdAt = new DateTime('2017-11-16 14:11:42', new DateTimeZone('UTC'));
        $this->updatedAt = new DateTime('2017-11-16 17:12:30', new DateTimeZone('UTC'));

        return TaskLogEntity::createFromArray([
            'id' => 'rdf#i1508337970199318643',
            'parent_id' => 'parentFake0002525',
            'task_name' => 'Task Name',
            'parameters' => json_encode(['param1' => 'value1', 'param2' => 'value2']),
            'label' => 'Task label',
            'status' => $status,
            'owner' => 'userId',
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'report' => [
                'type' => 'info',
                'message' => 'Running task http://www.taoinstance.dev/ontologies/tao.rdf#i1508337970199318643',
                'data' => null,
                'children' => []
            ],
            'master_status' => true
        ], DateTime::RFC3339);
    }

    protected function getFixtureEntityData($status = TaskLogInterface::STATUS_COMPLETED)
    {
        $status = CategorizedStatus::createFromString($status);

        return [
            'id' => 'rdf#i1508337970199318643',
            'taskName' => 'Task Name',
            'taskLabel' => 'Task label',
            'status' => (string) $status,
            'statusLabel' => $status->getLabel(),
            'report' => [
                'type' => 'info',
                'message' => 'Running task http://www.taoinstance.dev/ontologies/tao.rdf#i1508337970199318643',
                'data' => null,
                'children' => []
            ],
            'masterStatus' => true
        ];
    }
}
