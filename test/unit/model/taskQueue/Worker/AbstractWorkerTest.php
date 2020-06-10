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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\test\unit\model\taskQueue\Worker;

use common_report_Report;
use common_session_Session;
use common_user_User;
use core_kernel_classes_Resource;
use Exception;
use oat\generis\model\data\Ontology;
use oat\generis\model\user\UserFactoryServiceInterface;
use oat\generis\test\TestCase;
use common_report_Report as Report;
use oat\oatbox\log\LoggerService;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\QueuerInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\RemoteTaskSynchroniserInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\taskQueue\Worker\AbstractWorker;
use oat\tao\model\webhooks\task\WebhookTask;
use oat\generis\test\MockObject;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractWorkerTest extends TestCase
{
    /** @var QueuerInterface | MockObject */
    private $queue;

    /** @var TaskLogInterface | MockObject */
    private $taskLog;

    /** @var DummyWorker */
    private $subject;

    /**
     * @var ServiceLocatorInterface|MockObject
     */
    private $serviceLocatorMock;

    /** @var TaskInterface | MockObject $task */
    private $taskMock;

    /**
     * @var SessionService | MockObject
     */
    private $sessionServiceMock;

    /**
     * @var common_session_Session | MockObject
     */
    private $commonSession;
    /**
     * @var User | MockObject
     */
    private $userMock;
    /**
     * @var UserFactoryServiceInterface | MockObject
     */
    private $userFactoryServiceMock;

    /**
     * @var common_user_User | MockObject
     */
    private $commonUserMock;

    /**
     * @var Ontology | MockObject
     */
    private $modelMock;

    /**
     * @var core_kernel_classes_Resource | MockObject
     */
    private $userResourceMock;
    /**
     * @var LoggerService | MockObject
     */
    private $loggerServiceMock;

    /**
     * @var common_report_Report | MockObject
     */
    private $reportMock;
    /**
     * @var RemoteTaskSynchroniserInterface | MockObject
     */
    private $remoteTaskSynchroniserMock;

    /**
     * @var TaskLogBrokerInterface | MockObject
     */
    private $taskLogBrokerMock;

    /**
     * @var QueueDispatcherInterface | MockObject
     */
    private $queueDispatcherMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queue = $this->createMock(QueuerInterface::class);
        $this->taskLog = $this->createMock(TaskLogInterface::class);
        $this->sessionServiceMock = $this->createMock(SessionService::class);
        $this->userFactoryServiceMock = $this->createMock(UserFactoryServiceInterface::class);
        $this->loggerServiceMock = $this->createMock(LoggerService::class);
        $this->serviceLocatorMock = $this->getServiceLocatorMock([
            SessionService::class => $this->sessionServiceMock,
            UserFactoryServiceInterface::SERVICE_ID => $this->userFactoryServiceMock,
            LoggerService::SERVICE_ID => $this->loggerServiceMock,
        ]);

        $this->modelMock = $this->createMock(Ontology::class);
        $this->taskMock = $this->createMock(TaskInterface::class);
        $this->reportMock = $this->createMock(\common_report_Report::class);

        $this->subject = new DummyWorker($this->queue, $this->taskLog);
        $this->subject->setServiceLocator($this->serviceLocatorMock);
        $this->subject->setModel($this->modelMock);
    }

    public function testProcessTaskCancelled()
    {
        $this->taskLog->method('getStatus')->willReturn(TaskLogInterface::STATUS_CANCELLED);

        $this->taskLog->expects($this->once())->method('setReport')->willReturn(TaskLogInterface::STATUS_CANCELLED);

        $result = $this->subject->processTask($this->taskMock);
        $this->assertSame('cancelled', $result);
    }

    public function testProcessTaskHasParent()
    {
        $this->taskLog->method('setStatus')->willReturn(1);
        $this->taskMock->method('hasParent')->willReturn(true);
        $parentLogEntityMock = $this->createMock(EntityInterface::class);


        $this->taskLog->expects($this->once())->method('updateParent');
        $this->taskLog->expects($this->once())->method('getById')->willReturn($parentLogEntityMock);
        $parentLogEntityMock->expects($this->once())->method('isMasterStatus')->willReturn(false);

        $result = $this->subject->processTask($this->taskMock);
        $this->assertSame('completed', $result);
    }

    public function testProcessRemoteStatusFailed()
    {
        $this->taskMock = $this->getCallbackTask();
        $this->remoteTaskSynchroniserMock = $this->createMock(RemoteTaskSynchroniserInterface::class);
        $this->queue->method('enqueue')->willReturn(true);
        $this->taskLog->method('setStatus')->willReturn(1);
        $this->reportMock->method('getType')->willReturn(\common_report_Report::TYPE_INFO);
        $this->taskLog->method('getStatus')->willReturn(TaskLogInterface::STATUS_RUNNING);
        $this->remoteTaskSynchroniserMock = $this->createMock(RemoteTaskSynchroniserInterface::class);
        $this->remoteTaskSynchroniserMock->method('getRemoteStatus')->willReturn('failed');
        $this->taskMock->method('getCallable')->willReturn($this->remoteTaskSynchroniserMock);

        $this->queue->expects($this->once())->method('acknowledge');

        $result = $this->subject->processTask($this->taskMock);
        $this->assertSame('failed', $result);
    }

    public function testProcessRemoteTaskSynchroniser()
    {
        $this->taskMock = $this->getCallbackTask();
        $this->remoteTaskSynchroniserMock = $this->createMock(RemoteTaskSynchroniserInterface::class);
        $this->remoteTaskSynchroniserMock->method('getRemoteStatus')->willReturn('created');
        $this->queue->method('enqueue')->willReturn(true);
        $this->taskMock->method('getCallable')->willReturn($this->remoteTaskSynchroniserMock);
        $this->taskLogBrokerMock = $this->createMock(TaskLogBrokerInterface::class);
        $this->taskLog->method('getBroker')->willReturn($this->taskLogBrokerMock);
        $this->taskLog->method('getStatus')->willReturn(TaskLogInterface::STATUS_RUNNING);
        $this->taskLog->method('setStatus')->willReturn(1);
        $this->reportMock->method('getType')->willReturn(\common_report_Report::TYPE_INFO);
        $this->taskMock->method('__invoke')->willReturn($this->reportMock);
        $this->taskMock->method('getId')->willReturn('someStringId');

        $this->queue->expects($this->once())->method('count');
        $this->taskLogBrokerMock->expects($this->once())->method('deleteById');
        $this->queue->expects($this->once())->method('acknowledge');

        $result = $this->subject->processTask($this->taskMock);

        $this->assertSame('completed', $result);
    }

    public function testProcessTaskHasChildren()
    {
        $this->taskMock = $this->getTaskMockCallback();
        $this->taskLog->method('getStatus')->willReturn(TaskLogInterface::STATUS_RUNNING);
        $this->taskLog->method('setStatus')->willReturn(1);
        $this->reportMock->method('getType')->willReturn(\common_report_Report::TYPE_INFO);
        $this->taskMock->method('__invoke')->willReturn($this->reportMock);
        $this->taskMock->method('hasChildren')->willReturn(true);

        $result = $this->subject->processTask($this->taskMock);

        $this->assertSame('child_running', $result);
    }

    public function testProcessTaskReturnWarningReport()
    {
        $this->taskMock = $this->getTaskMockCallback();
        $this->taskLog->method('getStatus')->willReturn(TaskLogInterface::STATUS_RUNNING);
        $this->taskLog->method('setStatus')->willReturn(1);
        $this->reportMock->method('getType')->willReturn(\common_report_Report::TYPE_WARNING);
        $this->taskMock->method('__invoke')->willReturn($this->reportMock);


        $result = $this->subject->processTask($this->taskMock);
        $this->assertSame('completed', $result);
    }

    public function testProcessTaskReturnInfoReport()
    {
        $this->taskMock = $this->getTaskMockCallback();
        $this->taskLog->method('getStatus')->willReturn(TaskLogInterface::STATUS_RUNNING);
        $this->taskLog->method('setStatus')->willReturn(1);
        $this->reportMock->method('getType')->willReturn(\common_report_Report::TYPE_INFO);
        $this->taskMock->method('__invoke')->willReturn($this->reportMock);

        $this->queue->expects($this->once())->method('acknowledge');

        $result = $this->subject->processTask($this->taskMock);
        $this->assertSame('completed', $result);
    }

    public function testProcessTaskReturnErrorReport()
    {
        $this->taskMock = $this->getTaskMockCallback();
        $this->taskLog->method('getStatus')->willReturn(TaskLogInterface::STATUS_RUNNING);
        $this->taskLog->method('setStatus')->willReturn(1);
        $this->reportMock->method('getType')->willReturn(\common_report_Report::TYPE_ERROR);
        $this->taskMock->method('__invoke')->willReturn($this->reportMock);

        $this->queue->expects($this->once())->method('acknowledge');

        $result = $this->subject->processTask($this->taskMock);
        $this->assertSame('failed', $result);
    }

    public function testProcessTaskCatchException()
    {
        $this->taskMock = $this->getTaskMockCallback();
        $this->taskLog->method('getStatus')->willReturn(TaskLogInterface::STATUS_RUNNING);
        $this->taskLog->method('setStatus')->willReturn(1);

        $this->taskMock->method('__invoke')->willThrowException(new Exception('exception message'));
        $this->loggerServiceMock->expects($this->once())->method('error');

        $result = $this->subject->processTask($this->taskMock);
        $this->assertSame('failed', $result);
    }

    public function testProcessTaskInvokeNotReport()
    {
        $this->taskMock = $this->getTaskMockCallback();
        $this->taskLog->method('getStatus')->willReturn(TaskLogInterface::STATUS_RUNNING);
        $this->taskLog->method('setStatus')->willReturn(1);
        $this->taskMock->method('__invoke')->willReturn(true);

        $this->loggerServiceMock->expects($this->once())->method('warning');

        $result = $this->subject->processTask($this->taskMock);
        $this->assertSame('completed', $result);
    }

    public function testProcessCancelledTask()
    {
        /** @var TaskInterface | MockObject $task */
        $task = $this->createMock(TaskInterface::class);
        $task
            ->method('getId')
            ->willReturn('id');

        $this->taskLog
            ->expects($this->once())
            ->method('getStatus')
            ->with('id')
            ->willReturn(TaskLogInterface::STATUS_CANCELLED);
        $this->taskLog
            ->expects($this->once())
            ->method('setReport')
            ->with(
                'id',
                Report::createInfo('Task id has been cancelled, message was not processed.'),
                TaskLogInterface::STATUS_CANCELLED
            );

        $this->queue->expects($this->once())->method('acknowledge')->with($task);

        $this->assertEquals(TaskLogInterface::STATUS_CANCELLED, $this->subject->processTask($task));
    }

    public function testProcessTaskStartUserSession()
    {
        $this->taskMock->method('getOwner')->willReturn('ownerString');
        $this->commonSession = $this->createMock(common_session_Session::class);
        $this->sessionServiceMock->method('getCurrentSession')->willReturn($this->commonSession);
        $this->userMock = $this->createMock(User::class);
        $this->commonSession->method('getUser')->willReturn($this->userMock);
        $this->userMock->method('getIdentifier')->willReturn('userIdString');
        $this->userResourceMock = $this->createMock(core_kernel_classes_Resource::class);

        $this->modelMock->method('getResource')->willReturn($this->userResourceMock);
        $this->commonUserMock = $this->createMock(common_user_User::class);
        $this->userFactoryServiceMock->method('createUser')->willReturn($this->commonUserMock);

        $this->loggerServiceMock->expects($this->exactly(2))->method('info');
        $this->sessionServiceMock->expects($this->once())->method('setSession');

        $result = $this->subject->processTask($this->taskMock);
        $this->assertSame('unknown', $result);
    }

    private function getCallbackTask(): CallbackTaskInterface
    {
        return $this->createMock(CallbackTaskInterface::class);
    }

    /**
     * @return TaskInterface | MockObject
     */
    private function getTaskMockCallback()
    {
        return $this->createMock(TaskInterface::class);
    }
}

class DummyWorker extends AbstractWorker
{
    public function run()
    {
        return null;
    }
}
