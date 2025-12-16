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

namespace oat\tao\test\unit\model\taskQueue\TaskLog\Broker;

use oat\generis\test\PersistenceManagerMockTrait;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\taskQueue\Task\CallbackTask;
use oat\tao\model\taskQueue\TaskLog\Broker\RdsTaskLogBroker;
use common_persistence_Manager as PersistenceManager;
use oat\tao\model\taskQueue\TaskLog\Entity\TaskLogEntity;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLogInterface;

class RdsTaskLogBrokerTest extends TestCase
{
    use ServiceManagerMockTrait;
    use PersistenceManagerMockTrait;

    private RdsTaskLogBroker $subject;

    protected function setUp(): void
    {
        $persistenceId = 'rds_task_log_test';
        $databaseMock = $this->getPersistenceManagerMock($persistenceId);
        $persistence = $databaseMock->getPersistenceById($persistenceId);

        $persistenceManager = $this->getMockBuilder(PersistenceManager::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['getPersistenceById'])
        ->getMock();
        $persistenceManager
            ->method('getPersistenceById')
            ->with($persistenceId)
            ->willReturn($persistence);

        $serviceManagerMock = $this->getServiceManagerMock([
            PersistenceManager::SERVICE_ID => $persistenceManager,
        ]);

        $this->subject = new RdsTaskLogBroker($persistenceId);
        $this->subject->setServiceLocator($serviceManagerMock);

        $this->subject->createContainer();
    }

    public function testGetPersistenceWhenInstantiatingANewOneThenItReturnsOneWithTheRequiredInterface()
    {
        $commonPersistenceSqlPersistenceMock = $this
            ->getMockBuilder(\common_persistence_SqlPersistence::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commonPersistenceManagerMock = $this->getMockBuilder(\common_persistence_Manager::class)->getMock();

        $commonPersistenceManagerMock->expects($this->once())
            ->method('getPersistenceById')
            ->willReturn($commonPersistenceSqlPersistenceMock);

        $serviceManagerMock = $this->getMockBuilder(ServiceManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();

        $serviceManagerMock->expects($this->once())
            ->method('get')
            ->willReturn($commonPersistenceManagerMock);

        $rdsLogBrokerMock = $this->getMockBuilder(RdsTaskLogBroker::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getServiceLocator'])
            ->getMock();

        $rdsLogBrokerMock->expects($this->once())
            ->method('getServiceLocator')
            ->willReturn($serviceManagerMock);

        $persistenceCaller = function () {
            return $this->getPersistence();
        };

        // Bind the closure to $rdsLogBrokerMock's scope.
        // $bound is now a Closure, and calling it is like asking $rdsLogBrokerMock to call $this->getPersistence();
        // and return the results.
        $bound = $persistenceCaller->bindTo($rdsLogBrokerMock, $rdsLogBrokerMock);

        $this->assertInstanceOf(\common_persistence_SqlPersistence::class, $bound());
    }

    public function testGetTableNameWhenContainerNameIsSuppliedByOptionThenItShouldBeInTheTableName()
    {
        $prefix = 'tq';
        $containerName = 'example_container_name';

        $broker = new RdsTaskLogBroker('fakePersistence', $containerName);

        $tableNameCaller = function () {
            return $this->getTableName();
        };

        $bound = $tableNameCaller->bindTo($broker, $broker);

        $this->assertEquals($prefix . '_' . $containerName, $bound());
    }

    public function testGetTableNameWhenContainerNameIsNotSuppliedByOptionThenTableNameShouldHaveADefaultValue()
    {
        $prefix = 'tq';
        $defaultName = 'task_log';

        $broker = new RdsTaskLogBroker('fakePersistence');

        $tableNameCaller = function () {
            return $this->getTableName();
        };

        $bound = $tableNameCaller->bindTo($broker, $broker);

        $this->assertEquals($prefix . '_' . $defaultName, $bound());
    }

    public function testCountAndDelete()
    {
        $id = 'a random id';
        $owner = 'Owner name';
        $status = TaskLogInterface::STATUS_ENQUEUED;
        $label = 'this is a label';
        $createdAt = (new \DateTime('2019-06-22 10:11:12'))->setTimezone(new \DateTimeZone('UTC'));

        $task = new CallbackTask($id, $owner);
        $task->setCreatedAt($createdAt);

        $this->assertEquals(0, $this->subject->count(new TaskLogFilter()));
        $this->subject->add($task, $status, $label);
        $this->assertEquals(1, $this->subject->count(new TaskLogFilter()));
        $this->subject->deleteById($id);
        $this->assertEquals(0, $this->subject->count(new TaskLogFilter()));
    }

    public function testAdd()
    {
        $id = 'a random id';
        $owner = 'Owner name';
        $status = TaskLogInterface::STATUS_ENQUEUED;
        $label = 'this is a label';
        $createdAt = (new \DateTime('2019-06-22 10:11:12'))->setTimezone(new \DateTimeZone('UTC'));

        $task = new CallbackTask($id, $owner);
        $task->setCreatedAt($createdAt);

        $this->assertEquals(0, $this->subject->count(new TaskLogFilter()));
        $this->subject->add($task, $status, $label);
        $this->assertEquals(1, $this->subject->count(new TaskLogFilter()));
        foreach ($this->subject->search(new TaskLogFilter()) as $taskLogEntity) {
            if ($taskLogEntity instanceof TaskLogEntity) {
                $this->assertEquals($createdAt, $taskLogEntity->getCreatedAt());
            }
        }
    }
}
