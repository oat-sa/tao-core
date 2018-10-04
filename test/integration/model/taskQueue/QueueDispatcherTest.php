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

use oat\tao\model\taskQueue\Queue;
use oat\tao\model\taskQueue\Queue\Broker\InMemoryQueueBroker;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\Task\AbstractTask;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\test\Asset\CallableFixture;

class QueueDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  Queues needs to be set
     */
    public function testDispatcherWhenQueuesAreEmptyThenThrowException()
    {
        new QueueDispatcher([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectExceptionMessageRegExp  /There are duplicated Queue names/
     */
    public function testDispatcherWhenDuplicatedQueuesAreSetThenThrowException()
    {
        new QueueDispatcher([
            QueueDispatcher::OPTION_QUEUES =>[
                new Queue('queueA', new InMemoryQueueBroker()),
                new Queue('queueA', new InMemoryQueueBroker())
            ]
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectExceptionMessageRegExp  There are duplicated Queue names/
     */
    public function testDispatcherWhenNotRegisteredQueueIsUsedForTaskThenThrowException()
    {
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

        /** @var QueueDispatcher|\PHPUnit_Framework_MockObject_MockObject $queueMock */
        $queueMock = $this->getMockBuilder(QueueDispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['enqueue'])
            ->getMock();

        $queueMock->expects($this->once())
            ->method('enqueue')
            ->willReturn($this->returnValue(true));

        $this->assertInstanceOf(CallbackTaskInterface::class, $queueMock->createTask($taskMock, []) );
    }

    public function testCreateTaskWhenUsingStaticClassMethodCallShouldReturnCallbackTask()
    {
        /** @var QueueDispatcher|\PHPUnit_Framework_MockObject_MockObject $queueMock */
        $queueMock = $this->getMockBuilder(QueueDispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['enqueue'])
            ->getMock();

        $queueMock->expects($this->once())
            ->method('enqueue')
            ->willReturn($this->returnValue(true));

        $this->assertInstanceOf(CallbackTaskInterface::class, $queueMock->createTask([CallableFixture::class, 'exampleStatic'], []) );
    }
}