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

namespace oat\tao\test\unit\model\taskQueue;

use oat\tao\model\taskQueue\Queue;
use oat\tao\model\taskQueue\Queue\Broker\QueueBrokerInterface;
use oat\tao\model\taskQueue\Task\AbstractTask;
use oat\tao\model\taskQueue\TaskLogInterface;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectExceptionMessage  Queue name needs to be set.
     */
    public function testWhenQueueNameIsEmptyThenThrowException()
    {
        $brokerMock = $this->getMockForAbstractClass(QueueBrokerInterface::class);

        new Queue('', $brokerMock);
    }


    public function testGetNameShouldReturnTheValueOfQueueName()
    {
        $brokerMock = $this->getMockForAbstractClass(QueueBrokerInterface::class);

        $queue = new Queue('fakeQueue', $brokerMock);
        $this->assertEquals('fakeQueue', $queue->getName());
    }

    public function testGetWeightShouldReturnTheValueOfQueueWeight()
    {
        $brokerMock = $this->getMockForAbstractClass(QueueBrokerInterface::class);

        $queue = new Queue('fakeQueue', $brokerMock, 23);
        $this->assertEquals(23, $queue->getWeight());
    }

    /**
     * @dataProvider provideEnqueueOptions
     */
    public function testEnqueueWhenTaskPushedOrNot($isEnqueued, $expected)
    {
        $taskMock = $this->getMockForAbstractClass(AbstractTask::class, [], "", false);

        $queueBrokerMock = $this->getMockForAbstractClass(QueueBrokerInterface::class);

        $queueBrokerMock->expects($this->once())
            ->method('push')
            ->willReturn($isEnqueued);

        $taskLogMock = $this->getMockForAbstractClass(TaskLogInterface::class);

        /** @var Queue|\PHPUnit_Framework_MockObject_MockObject $queueMock */
        $queueMock = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBroker', 'getTaskLog'])
            ->getMock();

        $queueMock->expects($this->once())
            ->method('getBroker')
            ->willReturn($queueBrokerMock);

        if ($isEnqueued) {
            $taskLogMock->expects($this->once())
                ->method('add');

            $queueMock->expects($this->once())
                ->method('getTaskLog')
                ->willReturn($taskLogMock);
        }

        $this->assertEquals($expected, $queueMock->enqueue($taskMock));
    }

    public function provideEnqueueOptions()
    {
        return [
            'ShouldBeSuccessful' => [true, true],
            'ShouldBeFailed' => [false, false],
        ];
    }

    /**
     * @dataProvider provideDequeueOptions
     */
    public function testDequeueWhenTaskPoppedOrNot($dequeuedElem, $expected)
    {
        $queueBrokerMock = $this->getMockForAbstractClass(QueueBrokerInterface::class);

        $queueBrokerMock->expects($this->once())
            ->method('pop')
            ->willReturn($dequeuedElem);

        $taskLogMock = $this->getMockForAbstractClass(TaskLogInterface::class);

        /** @var Queue|\PHPUnit_Framework_MockObject_MockObject $queueMock */
        $queueMock = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBroker', 'getTaskLog'])
            ->getMock();

        $queueMock->expects($this->once())
            ->method('getBroker')
            ->willReturn($queueBrokerMock);

        if ($dequeuedElem) {
            $taskLogMock->expects($this->once())
                ->method('getStatus');

            $taskLogMock->expects($this->once())
                ->method('setStatus');

            $queueMock->expects($this->exactly(2))
                ->method('getTaskLog')
                ->willReturn($taskLogMock);
        }

        $this->assertEquals($expected, $queueMock->dequeue());
    }

    public function provideDequeueOptions()
    {
        $taskMock = $this->getMockForAbstractClass(AbstractTask::class, [], "", false);

        return [
            'ShouldBeSuccessful' => [$taskMock, $taskMock],
            'ShouldBeFailed' => [null, null],
        ];
    }

    public function testAcknowledgeShouldCallDeleteOnBroker()
    {
        $taskMock = $this->getMockForAbstractClass(AbstractTask::class, [], "", false);

        $queueBrokerMock = $this->getMockForAbstractClass(QueueBrokerInterface::class);

        $queueBrokerMock->expects($this->once())
            ->method('delete');

        /** @var Queue|\PHPUnit_Framework_MockObject_MockObject $queueMock */
        $queueMock = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBroker'])
            ->getMock();

        $queueMock->expects($this->once())
            ->method('getBroker')
            ->willReturn($queueBrokerMock);

        $queueMock->acknowledge($taskMock);
    }

    public function testCountShouldCallCountOnBroker()
    {
        $queueBrokerMock = $this->getMockForAbstractClass(QueueBrokerInterface::class);

        $queueBrokerMock->expects($this->once())
            ->method('count');

        /** @var Queue|\PHPUnit_Framework_MockObject_MockObject $queueMock */
        $queueMock = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBroker'])
            ->getMock();

        $queueMock->expects($this->once())
            ->method('getBroker')
            ->willReturn($queueBrokerMock);

        $queueMock->count();
    }

    public function testInitializeShouldCallCreateQueueOnBroker()
    {
        $queueBrokerMock = $this->getMockForAbstractClass(QueueBrokerInterface::class);

        $queueBrokerMock->expects($this->once())
            ->method('createQueue');

        /** @var Queue|\PHPUnit_Framework_MockObject_MockObject $queueMock */
        $queueMock = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBroker'])
            ->getMock();

        $queueMock->expects($this->once())
            ->method('getBroker')
            ->willReturn($queueBrokerMock);

        $queueMock->initialize();
    }
}
