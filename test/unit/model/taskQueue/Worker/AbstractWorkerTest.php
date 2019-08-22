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

use common_session_Session;
use common_user_User;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\user\UserFactoryServiceInterface;
use oat\generis\test\TestCase;
use common_report_Report as Report;
use oat\oatbox\log\LoggerService;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;
use oat\tao\model\taskQueue\QueuerInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\taskQueue\Worker\AbstractWorker;
use PHPUnit_Framework_MockObject_MockObject;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractWorkerTest extends TestCase
{
    /** @var QueuerInterface | PHPUnit_Framework_MockObject_MockObject */
    private $queue;

    /** @var TaskLogInterface | PHPUnit_Framework_MockObject_MockObject */
    private $taskLog;

    /** @var DummyWorker */
    private $subject;

    /**
     * @var ServiceLocatorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceLocatorMock;

    /** @var TaskInterface | PHPUnit_Framework_MockObject_MockObject $task */
    private $taskMock;

    /**
     * @var SessionService | PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionServiceMock;

    /**
     * @var common_session_Session | PHPUnit_Framework_MockObject_MockObject
     */
    private $commonSession;
    /**
     * @var User | PHPUnit_Framework_MockObject_MockObject
     */
    private $userMock;
    /**
     * @var UserFactoryServiceInterface | PHPUnit_Framework_MockObject_MockObject
     */
    private $userFactoryServiceMock;

    /**
     * @var common_user_User | PHPUnit_Framework_MockObject_MockObject
     */
    private $commonUserMock;

    /**
     * @var Ontology | PHPUnit_Framework_MockObject_MockObject
     */
    private $modelMock;

    /**
     * @var core_kernel_classes_Resource | PHPUnit_Framework_MockObject_MockObject
     */
    private $userResourceMock;
    /**
     * @var LoggerService | PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerServiceMock;

    /**
     * @var common_report_Report | PHPUnit_Framework_MockObject_MockObject
     */
    private $reportMock;

    protected function setUp()
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
            LoggerService::SERVICE_ID => $this->loggerServiceMock
        ]);

        $this->modelMock = $this->createMock(Ontology::class);

        $this->taskMock = $this->createMock(TaskInterface::class);

        $this->subject = new DummyWorker($this->queue, $this->taskLog);
        $this->subject->setServiceLocator($this->serviceLocatorMock);
        $this->subject->setModel($this->modelMock);
    }

    public function testProcessTaskInvokeNotReport()
    {
        $this->taskMock = $this->getTaskMockCallback();
        $this->taskLog->method('getStatus')->willReturn(TaskLogInterface::STATUS_RUNNING);
        $this->taskLog->method('setStatus')->willReturn(1);

        $this->loggerServiceMock->expects($this->once())->method('warning');
        $this->taskMock->method('__invoke')->willReturn(true);


        $result = $this->subject->processTask($this->taskMock);

        $this->assertSame('completed', $result);
    }

    public function testProcessCancelledTask()
    {
        /** @var TaskInterface | PHPUnit_Framework_MockObject_MockObject $task */
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

    /**
     * @return TaskInterface | PHPUnit_Framework_MockObject_MockObject
     */
    private function getTaskMockCallback() {
        $mock = $this
            ->getMockBuilder(TaskInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['__invoke', 'getStatus'])
            ->getMockForAbstractClass();
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
