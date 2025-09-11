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

namespace oat\tao\test\unit\model\taskQueue;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use oat\tao\model\taskQueue\Queue;
use oat\tao\model\taskQueue\Queue\Broker\QueueBrokerInterface;
use oat\tao\model\taskQueue\Task\AbstractTask;
use oat\tao\model\taskQueue\TaskLogInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockInterface;
use PHPUnit\Framework\MockObject\MockObject;

interface QueueBrokerInterfaceMock extends QueueBrokerInterface
{
    public function setServiceLocator();
}

class QueueTest extends TestCase
{
    public function testWhenQueueNameIsEmptyThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Queue name needs to be set.');
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
        $lockMock = $this->getMockBuilder(LockInterface::class)->disableOriginalConstructor()->getMock();
        $lockMock->method('acquire')->willReturn(true);
        $lockMock->method('release')->willReturn(true);
        $queueBrokerMock = $this->getMockForAbstractClass(QueueBrokerInterface::class);

        $queueBrokerMock->expects($this->once())
            ->method('push')
            ->willReturn($isEnqueued);

        $taskLogMock = $this->getMockForAbstractClass(TaskLogInterface::class);

        /** @var Queue|MockObject $queueMock */
        $queueMock = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBroker', 'getTaskLog', 'createLock'])
            ->getMock();

        $queueMock->expects($this->once())
            ->method('getBroker')
            ->willReturn($queueBrokerMock);
        $queueMock->expects($this->once())
            ->method('createLock')
            ->willReturn($lockMock);
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
        $queueBrokerMock =

        $lockMock = $this->getMockBuilder(LockInterface::class)->disableOriginalConstructor()->getMock();
        $lockMock->method('acquire')->willReturn(true);
        $lockMock->method('release')->willReturn(true);
        /** @var QueueBrokerInterfaceMock|MockObject $queueBrokerMock */
        $queueBrokerMock = $this->getMockBuilder(QueueBrokerInterfaceMock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['pop', 'setServiceLocator'])
            ->getMockForAbstractClass();

        $queueBrokerMock
            ->method('pop')
            ->willReturn($dequeuedElem);

        $queueName = 'name of the queue';
        $subject = $this->getMockBuilder(Queue::class)
            ->setConstructorArgs([$queueName, $queueBrokerMock])
            ->onlyMethods(['createLock'])
            ->getMock();
        $subject->method('createLock')
            ->willReturn($lockMock);

        if ($dequeuedElem instanceof AbstractTask) {
            /** @var TaskLogInterface|MockObject $taskLogMock */
            $taskLogMock = $this->getMockForAbstractClass(TaskLogInterface::class);
            $taskLogMock
                ->method('getStatus')
                ->willReturnArgument(0);

            if ($dequeuedElem->getId() !== TaskLogInterface::STATUS_CANCELLED) {
                $taskLogMock->expects($this->once())
                    ->method('setStatus')
                    ->with($dequeuedElem->getId(), TaskLogInterface::STATUS_DEQUEUED);

                /** @var LoggerInterface|MockObject $loggerMock */
                $loggerMock = $this->getMockBuilder(LoggerInterface::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(['info'])
                    ->getMockForAbstractClass();
                $loggerMock
                    ->expects($this->once())
                    ->method('info')
                    ->with(
                        sprintf('Task %s has been dequeued', $dequeuedElem->getId()),
                        [
                            'PID' => getmypid(),
                            'QueueName' => $queueName,
                        ]
                    );

                $subject->setLogger($loggerMock);
            }

            $subject->setTaskLog($taskLogMock);
        }

        $this->assertEquals($expected, $subject->dequeue());
    }

    public function provideDequeueOptions()
    {
        $validId = 'a valid id';

        $canceledTask = $this->createTaskMock(TaskLogInterface::STATUS_CANCELLED);
        $dequeuedTask = $this->createTaskMock($validId);

        return [
            'empty queue' => [null, null],
            'canceled task' => [$canceledTask, $canceledTask],
            'dequeued task' => [$dequeuedTask, $dequeuedTask],
        ];
    }

    public function createTaskMock($id)
    {
        $taskMock = $this->getMockBuilder(AbstractTask::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();

        $taskMock->method('getId')->willReturn($id);

        return $taskMock;
    }

    public function testAcknowledgeShouldCallDeleteOnBroker()
    {
        $taskMock = $this->getMockForAbstractClass(AbstractTask::class, [], "", false);

        $queueBrokerMock = $this->getMockForAbstractClass(QueueBrokerInterface::class);

        $queueBrokerMock->expects($this->once())
            ->method('delete');

        /** @var Queue|MockObject $queueMock */
        $queueMock = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBroker'])
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
            ->method('count')->willReturn(1);

        /** @var Queue|MockObject $queueMock */
        $queueMock = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBroker'])
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

        /** @var Queue|MockObject $queueMock */
        $queueMock = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBroker'])
            ->getMock();

        $queueMock->expects($this->once())
            ->method('getBroker')
            ->willReturn($queueBrokerMock);

        $queueMock->initialize();
    }
}
