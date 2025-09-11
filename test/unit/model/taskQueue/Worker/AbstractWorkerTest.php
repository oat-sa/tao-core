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
 * Copyright (c) 2019-2025 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\taskQueue\Worker;

use common_report_Report;
use common_session_Session;
use common_user_User;
use core_kernel_classes_Resource;
use Exception;
use oat\generis\model\data\Ontology;
use oat\generis\model\user\UserFactoryServiceInterface;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use common_report_Report as Report;
use oat\oatbox\log\LoggerService;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;
use oat\tao\model\taskQueue\QueuerInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\RemoteTaskSynchroniserInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\Task\TaskLanguageLoader;
use oat\tao\model\taskQueue\Task\TaskLanguageLoaderInterface;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\taskQueue\Worker\AbstractWorker;
use PHPUnit\Framework\MockObject\MockObject;

class AbstractWorkerTest extends TestCase
{
    use ServiceManagerMockTrait;

    private QueuerInterface|MockObject $queue;
    private TaskLogInterface|MockObject $taskLog;
    private DummyWorker $subject;
    private TaskInterface|MockObject $taskMock;
    private SessionService|MockObject $sessionServiceMock;
    private UserFactoryServiceInterface|MockObject $userFactoryServiceMock;
    private Ontology|MockObject $modelMock;
    private LoggerService|MockObject $loggerServiceMock;
    private common_report_Report|MockObject $reportMock;
    private RemoteTaskSynchroniserInterface|MockObject $remoteTaskSynchroniserMock;
    private TaskLanguageLoaderInterface|MockObject $taskLanguageLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queue = $this->createMock(QueuerInterface::class);
        $this->taskLog = $this->createMock(TaskLogInterface::class);
        $this->sessionServiceMock = $this->createMock(SessionService::class);
        $this->userFactoryServiceMock = $this->createMock(UserFactoryServiceInterface::class);
        $this->loggerServiceMock = $this->createMock(LoggerService::class);
        $this->taskLanguageLoader = $this->createMock(TaskLanguageLoaderInterface::class);

        $serviceLocatorMock = $this->getServiceManagerMock([
            SessionService::class => $this->sessionServiceMock,
            UserFactoryServiceInterface::SERVICE_ID => $this->userFactoryServiceMock,
            LoggerService::SERVICE_ID => $this->loggerServiceMock,
            TaskLanguageLoader::class => $this->taskLanguageLoader,
        ]);

        $this->modelMock = $this->createMock(Ontology::class);
        $this->reportMock = $this->createMock(\common_report_Report::class);
        $this->taskMock = $this->createMock(TaskInterface::class);
        $this->taskMock
            ->method('getLabel')
            ->willReturn('Task Label');

        $this->subject = new DummyWorker($this->queue, $this->taskLog);
        $this->subject->setServiceLocator($serviceLocatorMock);
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

        $this->taskLanguageLoader->expects($this->once())->method('loadTranslations')->with($this->taskMock);

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
        $this->taskLanguageLoader->expects($this->once())->method('loadTranslations')->with($this->taskMock);

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
        $taskLogBrokerMock = $this->createMock(TaskLogBrokerInterface::class);
        $this->taskLog->method('getBroker')->willReturn($taskLogBrokerMock);
        $this->taskLog->method('getStatus')->willReturn(TaskLogInterface::STATUS_RUNNING);
        $this->taskLog->method('setStatus')->willReturn(1);
        $this->reportMock->method('getType')->willReturn(\common_report_Report::TYPE_INFO);
        $this->taskMock->method('__invoke')->willReturn($this->reportMock);
        $this->taskMock->method('getId')->willReturn('someStringId');

        $this->queue->expects($this->once())->method('count');
        $taskLogBrokerMock->expects($this->once())->method('deleteById');
        $this->queue->expects($this->once())->method('acknowledge');
        $this->taskLanguageLoader->expects($this->once())->method('loadTranslations')->with($this->taskMock);

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
        $this->taskLanguageLoader->expects($this->once())->method('loadTranslations')->with($this->taskMock);

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
        $this->taskLanguageLoader->expects($this->once())->method('loadTranslations')->with($this->taskMock);

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

        $this->taskLanguageLoader->expects($this->once())->method('loadTranslations')->with($this->taskMock);
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

        $this->taskLanguageLoader->expects($this->once())->method('loadTranslations')->with($this->taskMock);
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
        $this->taskLanguageLoader->expects($this->once())->method('loadTranslations')->with($this->taskMock);

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
        $this->taskLanguageLoader->expects($this->once())->method('loadTranslations')->with($this->taskMock);

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
        $commonSession = $this->createMock(common_session_Session::class);
        $this->sessionServiceMock->method('getCurrentSession')->willReturn($commonSession);
        $userMock = $this->createMock(User::class);
        $commonSession->method('getUser')->willReturn($userMock);
        $userMock->method('getIdentifier')->willReturn('userIdString');
        $userResourceMock = $this->createMock(core_kernel_classes_Resource::class);

        $this->modelMock->method('getResource')->willReturn($userResourceMock);
        $commonUserMock = $this->createMock(common_user_User::class);
        $this->userFactoryServiceMock->method('createUser')->willReturn($commonUserMock);

        $this->loggerServiceMock->expects($this->exactly(2))->method('info');
        $this->sessionServiceMock->expects($this->once())->method('setSession');

        $result = $this->subject->processTask($this->taskMock);
        $this->assertSame('unknown', $result);
    }

    private function getCallbackTask(): CallbackTaskInterface
    {
        $mock = $this->createMock(CallbackTaskInterface::class);
        $mock->method('getLabel')->willReturn('Task Label');

        return $mock;
    }

    /**
     * @return TaskInterface | MockObject
     */
    private function getTaskMockCallback()
    {
        $mock = $this->createMock(TaskInterface::class);
        $mock->method('getLabel')->willReturn('Task Label');

        return $mock;
    }
}

class DummyWorker extends AbstractWorker
{
    public function run()
    {
        return null;
    }
}
