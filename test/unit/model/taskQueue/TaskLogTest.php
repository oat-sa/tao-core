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
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\taskQueue;

use common_exception_NotFound;
use Exception;
use InvalidArgumentException;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use oat\tao\model\taskQueue\Task\AbstractTask;
use oat\tao\model\taskQueue\TaskLog;
use oat\tao\model\taskQueue\TaskLog\Broker\RdsTaskLogBroker;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\TaskLogEntity;
use oat\tao\model\taskQueue\TaskLog\TaskLogCollection;
use oat\tao\model\taskQueue\TaskLog\TasksLogsStats;
use oat\tao\model\taskQueue\TaskLogInterface;

class TaskLogTest extends TestCase
{
    use ServiceManagerMockTrait;

    /** @var TaskLog */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new TaskLog(
            [
                'task_log_broker' => new RdsTaskLogBroker('fakePersistence', 'fake'),
            ]
        );
    }

    public function testTaskLogServiceShouldThrowExceptionWhenTaskLogBrokerOptionIsNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        new TaskLog([]);
    }

    public function testGetBrokerInstantiatingTheTaskLogBrokerAndReturningItWithTheRequiredInterface()
    {
        $logBrokerMock = $this->getMockBuilder(TaskLogBrokerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setServiceLocator'])
            ->getMockForAbstractClass();

        $logBrokerMock->expects($this->once())
            ->method('setServiceLocator');

        $taskLogMock = $this->getMockBuilder(TaskLog::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOption', 'getServiceLocator'])
            ->getMock();

        $taskLogMock->expects($this->once())
            ->method('getOption')
            ->willReturn($logBrokerMock);

        $taskLogMock->expects($this->once())
            ->method('getServiceLocator')
            ->willReturn($this->getServiceManagerMock());

        $brokerCaller = function () {
            return $this->getBroker();
        };

        $bound = $brokerCaller->bindTo($taskLogMock, $taskLogMock);

        $this->assertInstanceOf(TaskLogBrokerInterface::class, $bound());
    }

    public function testAddWhenWrongStatusIsSuppliedThenErrorMessageShouldBeLogged()
    {
        $taskMock = $this->getMockForAbstractClass(AbstractTask::class, [], "", false);

        $taskLogMock = $this->getMockBuilder(TaskLog::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['logError'])
            ->getMock();

        $taskLogMock->expects($this->atLeastOnce())
            ->method('logError');

        $taskLogMock->add($taskMock, 'fake_status');
    }

    public function testAddWhenStatusIsOkayThenTaskShouldBeAddedByBroker()
    {
        $taskMock = $this->getMockForAbstractClass(AbstractTask::class, [], "", false);

        $logBrokerMock = $this->getMockForAbstractClass(TaskLogBrokerInterface::class);

        $logBrokerMock->expects($this->once())
            ->method('add');

        $taskLogMock = $this->getMockBuilder(TaskLog::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBroker'])
            ->getMock();

        $taskLogMock->expects($this->once())
            ->method('getBroker')
            ->willReturn($logBrokerMock);
        ;

        $taskLogMock->add($taskMock, 'enqueued');
    }

    public function testSetStatusWhenNewAndPrevStatusIsOkayThenStatusShouldBeUpdatedByBroker()
    {
        $logBrokerMock = $this->getMockForAbstractClass(TaskLogBrokerInterface::class);

        $logBrokerMock->expects($this->once())
            ->method('updateStatus');

        $taskLogMock = $this->getMockBuilder(TaskLog::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBroker', 'validateStatus'])
            ->getMock();

        $taskLogMock->expects($this->once())
            ->method('getBroker')
            ->willReturn($logBrokerMock);

        $taskLogMock->expects($this->exactly(2))
            ->method('validateStatus');

        $taskLogMock->setStatus('fakeId', 'dequeued', 'running');
    }

    public function testGetStatusWhenTaskExistItReturnsItsStatus()
    {
        $expectedStatus = 'dequeued';

        $logBrokerMock = $this->getMockForAbstractClass(TaskLogBrokerInterface::class);

        $logBrokerMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($expectedStatus);

        $taskLogMock = $this->getMockBuilder(TaskLog::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBroker', 'validateStatus'])
            ->getMock();

        $taskLogMock->expects($this->once())
            ->method('getBroker')
            ->willReturn($logBrokerMock);

        $this->assertEquals($expectedStatus, $taskLogMock->getStatus('existingTaskId'));
    }

    public function testFindAvailableByUser()
    {
        $model = $this->getTaskLogMock();
        $this->assertInstanceOf(TaskLogCollection::class, $model->findAvailableByUser('userId'));
    }

    public function testGetByIdAndUser()
    {
        $model = $this->getTaskLogMock();
        $this->assertInstanceOf(TaskLogEntity::class, $model->getByIdAndUser('taskId', 'userId'));
    }

    public function testGetByIdAndUserNotFound()
    {
        $this->expectException(common_exception_NotFound::class);
        $model = $this->getTaskLogMock(true);
        $this->assertInstanceOf(TaskLogEntity::class, $model->getByIdAndUser('some task id not found', 'userId'));
    }

    public function testGetStats()
    {
        $model = $this->getTaskLogMock();
        $this->assertInstanceOf(TasksLogsStats::class, $model->getStats('userId'));
    }

    public function testArchive()
    {
        $model = $this->getTaskLogMock();
        $this->assertTrue($model->archive($model->getByIdAndUser('taskId', 'userId')));
    }

    public function testArchiveTaskNotFound()
    {
        $this->expectException(common_exception_NotFound::class);
        $model = $this->getTaskLogMock(true);
        $this->assertTrue($model->archive($model->getByIdAndUser('taskId', 'userId')));
    }

    public function testArchiveNotPossibleIfTaskIsRunning()
    {
        $this->expectException(Exception::class);
        $model = $this->getTaskLogMock(false, false, true);

        $this->assertTrue($model->archive($model->getByIdAndUser('taskId', 'userId')));
    }

    public function testCancel()
    {
        $model = $this->getTaskLogMock();
        $this->assertTrue($model->cancel($model->getByIdAndUser('taskId', 'userId')));
    }

    public function testCancelTaskNotFound()
    {
        $this->expectException(common_exception_NotFound::class);
        $model = $this->getTaskLogMock(true);
        $this->assertTrue($model->cancel($model->getByIdAndUser('taskId', 'userId')));
    }

    public function testCancelNotPossibleIfTaskIsRunning()
    {
        $this->expectException(Exception::class);
        $model = $this->getTaskLogMock(false, false, true, false);

        $this->assertTrue($model->cancel($model->getByIdAndUser('taskId', 'userId')));
    }

    /**
     * @param bool $notFound
     * @param bool $shouldArchive
     * @param bool $taskRunning
     * @return MockObject|TaskLogInterface
     */
    protected function getTaskLogMock(
        $notFound = false,
        $shouldArchive = true,
        $taskRunning = false,
        $shouldCancel = true
    ) {
        $taskLogMock = $this->getMockBuilder(TaskLog::class)->disableOriginalConstructor()->getMock();
        $collectionMock = $this->getMockBuilder(TaskLogCollection::class)->disableOriginalConstructor()->getMock();
        $entity = $this->getMockBuilder(TaskLogEntity::class)->disableOriginalConstructor()->getMock();
        $statsMock = $this->getMockBuilder(TasksLogsStats::class)->disableOriginalConstructor()->getMock();

        $taskLogMock
            ->method('findAvailableByUser')
            ->willReturn($collectionMock);
        $taskLogMock
            ->method('getStats')
            ->willReturn($statsMock);
        if ($taskRunning) {
            $taskLogMock
                ->method('getByIdAndUser')
                ->willThrowException(new Exception());
        } else {
            $taskLogMock
                ->method('archive')
                ->willReturn($shouldArchive);

            $taskLogMock
                ->method('cancel')
                ->willReturn($shouldCancel);
        }
        if ($notFound) {
            $taskLogMock
                ->method('getByIdAndUser')
                ->willThrowException(new common_exception_NotFound());
        } else {
            $taskLogMock
                ->method('getByIdAndUser')
                ->willReturn($entity);
        }

        return $taskLogMock;
    }

    public function testTaskCategoryWithExactName()
    {
        $model = new TaskLog([
            'task_log_broker' => $broker = new RdsTaskLogBroker('fakePersistence', 'fake')
        ]);
        $model->linkTaskToCategory('Test\FakeClassName', 'import');

        $this->assertSame('import', $model->getCategoryForTask('Test\FakeClassName'));
    }

    public function testTaskCategoryWithSubClass()
    {
        $model = new TaskLog([
            'task_log_broker' => $broker = new RdsTaskLogBroker('fakePersistence', 'fake')
        ]);
        $model->linkTaskToCategory(StubTaskParent::class, 'export');

        $this->assertSame('export', $model->getCategoryForTask(StubTaskChild::class));
    }

    public function testTaskCategoryForUnknown()
    {
        $model = new TaskLog([
            'task_log_broker' => $broker = new RdsTaskLogBroker('fakePersistence', 'fake')
        ]);
        $model->linkTaskToCategory('Fake\Classname', 'export');

        $this->assertSame('unknown', $model->getCategoryForTask('ClassName\Which\Not\Added\Ever'));
    }

    public function testGetTaskCategories(): void
    {
        $this->assertSame(
            [
                TaskLogInterface::CATEGORY_CREATE,
                TaskLogInterface::CATEGORY_UPDATE,
                TaskLogInterface::CATEGORY_DELETE,
                TaskLogInterface::CATEGORY_COPY,
                TaskLogInterface::CATEGORY_IMPORT,
                TaskLogInterface::CATEGORY_EXPORT,
                TaskLogInterface::CATEGORY_DELIVERY_COMPILATION,
                TaskLogInterface::CATEGORY_UNRELATED_RESOURCE,
            ],
            $this->subject->getTaskCategories()
        );
    }
}

class StubTaskChild extends StubTaskParent
{
}
abstract class StubTaskParent
{
}
