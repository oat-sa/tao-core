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

use oat\generis\test\TestCase;
use oat\tao\model\taskQueue\Queue;
use oat\tao\model\taskQueue\Queue\Broker\QueueBrokerInterface;
use oat\tao\model\taskQueue\Task\AbstractTask;
use oat\tao\model\taskQueue\TaskLogInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockInterface;
use oat\generis\test\MockObject;

class QueueTest extends TestCase
{
    /**
     * @markTestSkipped
     * @dataProvider provideDequeueOptions
     */
    public function testDequeueWhenTaskPoppedOrNot($dequeuedElem, $expected)
    {
        $lockMock = $this->getMockBuilder(LockInterface::class)->disableOriginalConstructor()->getMock();
        $lockMock->method('acquire')->willReturn(true);
        $lockMock->method('release')->willReturn(true);
        /** @var QueueBrokerInterface|MockObject $queueBrokerMock */
        $queueBrokerMock = $this->getMockBuilder(QueueBrokerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['pop', 'setServiceLocator'])
            ->getMockForAbstractClass();

        $queueBrokerMock
            ->method('pop')
            ->willReturn($dequeuedElem);

        $queueName = 'name of the queue';
        $subject = $this->getMockBuilder(Queue::class)
            ->setConstructorArgs([$queueName, $queueBrokerMock])
            ->setMethods(['createLock'])
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
                    ->setMethods(['info'])
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
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $taskMock->method('getId')->willReturn($id);

        return $taskMock;
    }
}
