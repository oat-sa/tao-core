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

namespace oat\tao\test\integration\model\taskQueue;

use common_exception_Error;
use InvalidArgumentException;
use oat\generis\test\TestCase;
use oat\oatbox\mutex\LockService;
use oat\tao\model\taskQueue\Task\TaskSerializerService;
use oat\tao\model\taskQueue\Queue;
use oat\tao\model\taskQueue\Queue\Broker\InMemoryQueueBroker;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\Task\AbstractTask;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\test\Asset\CallableFixture;
use oat\oatbox\log\LoggerService;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\taskQueue\TaskLog;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\LockInterface;
use oat\generis\test\MockObject;

class QueueDispatcherTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDispatcherWhenQueuesAreEmptyThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Queues needs to be set');
        new QueueDispatcher([]);
    }

    public function testDispatcherNoTaskLogThenThrowException()
    {
        $this->expectException(common_exception_Error::class);
        $this->expectExceptionMessage('Task Log service needs to be set.');
        new QueueDispatcher([
            QueueDispatcher::OPTION_QUEUES => [
                new Queue('queueA', new InMemoryQueueBroker())
            ]
        ]);
    }

    public function testDispatcherWhenDuplicatedQueuesAreSetThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('/There are duplicated Queue names/');
        new QueueDispatcher([
            QueueDispatcher::OPTION_QUEUES => [
                new Queue('queueA', new InMemoryQueueBroker()),
                new Queue('queueA', new InMemoryQueueBroker())
            ]
        ]);
    }

    public function testDispatcherWhenNotRegisteredQueueIsUsedForTaskThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There are duplicated Queue names/');
        new QueueDispatcher([
            QueueDispatcher::OPTION_QUEUES => [
                new Queue('queueA', new InMemoryQueueBroker()),
                new Queue('queueA', new InMemoryQueueBroker())
            ],
            QueueDispatcher::OPTION_TASK_TO_QUEUE_ASSOCIATIONS => [
                'fake/class/name' => 'fake_queue_name'
            ]
        ]);
    }

    public function testCreateTaskWhenUsingANewTaskImplementingTaskInterfaceShouldReturnCallbackTask()
    {
        $taskMock = $this->getMockForAbstractClass(AbstractTask::class, [], "", false);

        /** @var QueueDispatcher|MockObject $queueMock */
        $queueMock = $this->getMockBuilder(QueueDispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['enqueue'])
            ->getMock();

        $queueMock->expects($this->once())
            ->method('enqueue')
            ->willReturn($this->returnValue(true));

        $this->assertInstanceOf(CallbackTaskInterface::class, $queueMock->createTask($taskMock, []));
    }

    public function testCreateTaskWhenUsingStaticClassMethodCallShouldReturnCallbackTask()
    {
        /** @var QueueDispatcher|MockObject $queueMock */
        $queueMock = $this->getMockBuilder(QueueDispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['enqueue'])
            ->getMock();

        $queueMock->expects($this->once())
            ->method('enqueue')
            ->willReturn($this->returnValue(true));

        $this->assertInstanceOf(CallbackTaskInterface::class, $queueMock->createTask([CallableFixture::class, 'exampleStatic'], []));
    }

    public function testOneTimeWorkerHasServiceLocator()
    {
        $taskMock = $this->getMockForAbstractClass(AbstractTask::class, [], "", false);
        $taskLogMock = $this->getMockBuilder(TaskLog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lock = $this->getMockBuilder(LockInterface::class)->disableOriginalConstructor()->getMock();
        $lock->method('acquire')->willReturn(true);
        $lock->method('release')->willReturn(true);

        $lockFactory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $lockFactory->method('createLock')->willReturn($lock);

        $lockService = $this->getMockBuilder(LockService::class)->disableOriginalConstructor()->getMock();
        $lockService->method('getLockFactory')->willReturn($lockFactory);
        $serviceManager = $this->getServiceLocatorMock([
            TaskLogInterface::SERVICE_ID => $taskLogMock,
            LoggerService::SERVICE_ID => $this->createMock(LoggerService::class),
            TaskSerializerService::SERVICE_ID => $this->createMock(TaskSerializerService::class),
            LockService::SERVICE_ID => $lockService
        ]);


        $dispatcher = new QueueDispatcher([
            QueueDispatcher::OPTION_QUEUES => [
                new Queue('queueA', new InMemoryQueueBroker())
            ],
            QueueDispatcher::OPTION_TASK_LOG => 'tao/taskLog',
        ]);

        $dispatcher->setServiceLocator($serviceManager);

        $this->assertTrue($dispatcher->enqueue($taskMock));
    }
}
