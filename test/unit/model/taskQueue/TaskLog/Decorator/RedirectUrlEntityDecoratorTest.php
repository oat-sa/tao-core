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

namespace oat\tao\test\unit\model\taskQueue\TaskLog\Decorator;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use oat\oatbox\user\AnonymousUser;
use oat\tao\model\taskQueue\TaskLog;
use oat\tao\model\taskQueue\TaskLog\CategorizedStatus;
use oat\tao\model\taskQueue\TaskLog\Decorator\RedirectUrlEntityDecorator;
use oat\tao\model\taskQueue\TaskLog\Entity\TaskLogEntity;
use oat\tao\model\taskQueue\TaskLogInterface;
use PHPUnit\Framework\TestCase;

class RedirectUrlEntityDecoratorTest extends TestCase
{
    /**
     * @dataProvider taskLogStatusProvider
     */
    public function testDecoratorNotUsedExcludedTaskLogStatus($taskStatus): void
    {
        $entity = $this->getFixtureEntity();

        $taskLog = $this->createMock(TaskLog::class);
        $taskLog
            ->method('getCategoryForTask')
            ->with('Task Name')
            ->willReturn($taskStatus);

        $decorator = new RedirectUrlEntityDecorator($entity, $taskLog, new AnonymousUser());

        $this->assertEquals($this->getFixtureEntityData(), $decorator->toArray());
    }

    public function taskLogStatusProvider(): array
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
     */
    public function testDecoratorNotUsedExcludedTaskEntityStatus($status): void
    {
        $entity = $this->getFixtureEntity($status);

        $taskLog = $this->createMock(TaskLog::class);
        $taskLog
            ->method('getCategoryForTask')
            ->with('Task Name')
            ->willReturn('notExcludedStatus');

        $redirectUrlEntityDecoratorMock = $this->getMockBuilder(RedirectUrlEntityDecorator::class)
            ->setConstructorArgs([$entity, $taskLog, new AnonymousUser()])
            ->onlyMethods(['hasAccess'])
            ->getMock();
        $redirectUrlEntityDecoratorMock->expects($this->once())->method('hasAccess')->willReturn(false);

        $this->assertEquals($this->getFixtureEntityData($status), $redirectUrlEntityDecoratorMock->toArray());
    }

    public function entityTaskLogStatusProvider(): array
    {
        return [
            [TaskLogInterface::STATUS_COMPLETED],
            [TaskLogInterface::STATUS_ARCHIVED],
        ];
    }

    public function testDecoratorUsed(): void
    {
        $entity = $this->getFixtureEntity();

        $taskLog = $this->createMock(TaskLog::class);
        $taskLog
            ->method('getCategoryForTask')
            ->with('Task Name')
            ->willReturn('notExcludedStatus');

        $redirectUrlEntityDecoratorMock = $this->getMockBuilder(RedirectUrlEntityDecorator::class)
            ->setConstructorArgs([$entity, $taskLog, new AnonymousUser()])
            ->onlyMethods(['hasAccess'])
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

    protected function getFixtureEntity($status = TaskLogInterface::STATUS_COMPLETED): TaskLogEntity
    {
        $createdAt = new DateTime('2017-11-16 14:11:42', new DateTimeZone('UTC'));
        $updatedAt = new DateTime('2017-11-16 17:12:30', new DateTimeZone('UTC'));

        return TaskLogEntity::createFromArray(
            [
                'id' => 'rdf#i1508337970199318643',
                'parent_id' => 'parentFake0002525',
                'task_name' => 'Task Name',
                'parameters' => json_encode(['param1' => 'value1', 'param2' => 'value2']),
                'label' => 'Task label',
                'status' => $status,
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
            ],
            DateTimeInterface::RFC3339
        );
    }

    protected function getFixtureEntityData($status = TaskLogInterface::STATUS_COMPLETED): array
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
