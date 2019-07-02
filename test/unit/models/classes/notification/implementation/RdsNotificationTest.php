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
 */

namespace oat\tao\test\unit\model\notification\implementation;

use common_persistence_Manager as PersistenceManager;
use oat\generis\test\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\notification\implementation\Notification;
use oat\tao\model\notification\implementation\RdsNotification;
use oat\tao\scripts\install\InstallNotificationTable;

class RdsNotificationTest extends TestCase
{
    /** @var RdsNotification */
    private $subject;

    /** @var \common_persistence_Persistence */
    private $persistence;

    public function setUp()
    {
        $persistenceId = 'rds_notification_test';
        $databaseMock = $this->getSqlMock($persistenceId);
        $this->persistence = $databaseMock->getPersistenceById($persistenceId);

        $persistenceManager = $this->getMockBuilder(PersistenceManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPersistenceById'])
            ->getMock();
        $persistenceManager->method('getPersistenceById')->willReturn($this->persistence);

        $serviceManagerMock = $this->getServiceLocatorMock([
            PersistenceManager::SERVICE_ID => $persistenceManager,
        ]);

        $this->subject = new RdsNotification([RdsNotification::OPTION_PERSISTENCE => $persistenceId]);
        $this->subject->setServiceLocator($serviceManagerMock);

        /** @var ServiceManager|\PHPUnit_Framework_MockObject_MockObject $serviceLocator */
        $serviceLocator = $this->getMockBuilder(ServiceManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'register'])
            ->getMock();
        $serviceLocator->method('get')->willReturn($persistenceManager);
        $serviceLocator->expects($this->once())->method('register');

        $tableCreator = new InstallNotificationTable();
        $tableCreator->setServiceLocator($serviceLocator);
        $tableCreator([]);
    }

    public function testGetNotificationsWithoutLinesReturnsEmptyArray()
    {
        $userId = 'id of the user';
        $this->assertEquals([], $this->subject->getNotifications($userId));
    }

    public function testSendNotificationAndGetNotifications()
    {
        $recipientId = 'id of the recipient';
        $title = 'the title';
        $message = 'this is the message';
        $senderId = 'id of the sender';
        $senderName = 'name of the sender';
        $id = 1;
        $createdAt = $this->persistence->getPlatform()->getNowExpression();
        $updatedAt = $this->persistence->getPlatform()->getNowExpression();
        $status = 12;

        $notification = new Notification($recipientId, $title, $message, $senderId, $senderName, $id, $createdAt, $updatedAt, $status);
        $this->subject->sendNotification($notification);
        $this->assertEquals([$notification], $this->subject->getNotifications($recipientId));
    }
}
