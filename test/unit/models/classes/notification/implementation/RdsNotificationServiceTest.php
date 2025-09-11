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

namespace oat\tao\test\unit\models\classes\notification\implementation;

use common_persistence_SqlPersistence;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use oat\generis\persistence\PersistenceManager;
use common_persistence_Persistence as Persistence;
use oat\generis\persistence\sql\SchemaCollection;
use oat\generis\test\OntologyMockTrait;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\notification\implementation\AbstractSqlNotificationService;
use oat\tao\model\notification\implementation\RdsNotificationService;
use oat\tao\model\notification\Notification;
use PHPUnit\Framework\MockObject\MockObject;

class RdsNotificationServiceTest extends TestCase
{
    use OntologyMockTrait;
    use ServiceManagerMockTrait;

    private const NOTIFICATION_ID = 'someId';
    private const NOW_EXPRESSION = 'someStringWithCurrentDate';
    private const RECIPIENT = 'recipient string';
    private const STATUS = 0;
    private const SENDER_ID = 'sender_id';
    private const SENDER_NAME = 'sender name';
    private const TITLE = 'notification table';
    private const MESSAGE = 'notification message';
    private const EXAMPLE_USER_ID = 'id12345';
    private const NOTIFICATION_COUNT = 777;

    private const RECIPIENT_EXAMPLE_1 = 'recipient_example_1';
    private const TITLE_EXAMPLE_1 = 'title_example_1';
    private const STATUS_EXAMPLE_1 = 0;
    private const SENDER_ID_EXAMPLE_1 = 'sender_id_example_1';
    private const SENDER_NAME_EXAMPLE_1 = 'sender_name_example_1';
    private const MESSAGE_EXAMPLE_1 = 'message_example_1';
    private const CREATED_AT_EXAMPLE_1 = 'created_at_example_1';
    private const UPDATED_AT_EXAMPLE_1 = 'updated_at_example_1';
    private const NOTIFICATION_ID_EXAMPLE_1 = 'some_notification_id_1';

    private const RECIPIENT_EXAMPLE_2 = 'recipient_example_2';
    private const TITLE_EXAMPLE_2 = 'title_example_2';
    private const STATUS_EXAMPLE_2 = 1;
    private const SENDER_ID_EXAMPLE_2 = 'sender_id_example_2';
    private const SENDER_NAME_EXAMPLE_2 = 'sender_name_example_2';
    private const MESSAGE_EXAMPLE_2 = 'message_example_2';
    private const CREATED_AT_EXAMPLE_2 = 'created_at_example_2';
    private const UPDATED_AT_EXAMPLE_2 = 'updated_at_example_2';
    private const NOTIFICATION_ID_EXAMPLE_2 = 'some_notification_id_2';

    private RdsNotificationService $subject;
    private Notification|MockObject $notificationMock;
    private Persistence|MockObject $persistenceMock;
    private AbstractPlatform|MockObject $platformMock;

    protected function setUp(): void
    {
        $this->persistenceMock = $this->createMock(common_persistence_SqlPersistence::class);
        $this->notificationMock = $this->createMock(Notification::class);

        $persistenceManagerMock = $this->createMock(PersistenceManager::class);
        $persistenceManagerMock->method('getPersistenceById')->willReturn($this->persistenceMock);

        $this->platformMock = $this->createMock(AbstractPlatform::class);
        $this->persistenceMock->method('getPlatForm')->willReturn($this->platformMock);

        $serviceLocatorMock = $this->getServiceManagerMock([
            PersistenceManager::SERVICE_ID => $persistenceManagerMock,
        ]);

        $ontologyMock = $this->getOntologyMock();

        $this->subject = new RdsNotificationService();
        $this->subject->setServiceLocator($serviceLocatorMock);
        $this->subject->setModel($ontologyMock);
    }

    public function testChangeStatus(): void
    {
        $this->notificationMock->expects($this->once())->method('getStatus')->willReturn(Notification::DEFAULT_STATUS);
        $this->notificationMock->expects($this->once())->method('getId')->willReturn(self::NOTIFICATION_ID);
        $this->platformMock->expects($this->once())->method('getNowExpression')->willReturn(self::NOW_EXPRESSION);
        $this->persistenceMock->expects($this->once())->method('exec')->with(
            'UPDATE notifications SET updated_at = ? ,status = ?  WHERE id = ? ',
            [
                self::NOW_EXPRESSION,
                Notification::DEFAULT_STATUS,
                self::NOTIFICATION_ID,
            ]
        )->willReturn(1);

        $result = $this->subject->changeStatus($this->notificationMock);
        $this->assertSame(true, $result);
    }

    public function testProvideSchema(): void
    {
        $tableMock = $this->createMock(Table::class);
        $schemaMock = $this->createMock(Schema::class);
        $schemaCollectionMock = $this->createMock(SchemaCollection::class);
        $schemaCollectionMock->expects($this->once())->method('getSchema')->willReturn($schemaMock);
        $schemaMock->expects($this->once())->method('createTable')->willReturn($tableMock);

        $tableMock->expects($this->once())->method('addOption')->with('engine', 'MyISAM');
        $tableMock->expects($this->exactly(9))->method('addColumn');
        $tableMock->expects($this->once())->method('setPrimaryKey');

        $this->subject->provideSchema($schemaCollectionMock);
    }

    public function testSendNotification(): void
    {
        $this->persistenceMock->expects($this->once())->method('insert')->with(
            AbstractSqlNotificationService::NOTIFICATION_TABLE,
            [
                AbstractSqlNotificationService::NOTIFICATION_FIELD_RECIPIENT => self::RECIPIENT,
                AbstractSqlNotificationService::NOTIFICATION_FIELD_STATUS => self::STATUS,
                AbstractSqlNotificationService::NOTIFICATION_FIELD_SENDER => self::SENDER_ID,
                AbstractSqlNotificationService::NOTIFICATION_FIELD_SENDER_NAME => self::SENDER_NAME,
                AbstractSqlNotificationService::NOTIFICATION_FIELD_TITLE => self::TITLE,
                AbstractSqlNotificationService::NOTIFICATION_FIELD_MESSAGE => self::MESSAGE,
                AbstractSqlNotificationService::NOTIFICATION_FIELD_CREATION => self::NOW_EXPRESSION,
                AbstractSqlNotificationService::NOTIFICATION_FIELD_UPDATED => self::NOW_EXPRESSION,
            ]
        );

        $this->notificationMock->expects($this->once())->method('getRecipient')->willReturn(self::RECIPIENT);
        $this->notificationMock->expects($this->once())->method('getStatus')->willReturn(self::STATUS);
        $this->notificationMock->expects($this->once())->method('getSenderId')->willReturn(self::SENDER_ID);
        $this->notificationMock->expects($this->once())->method('getSenderName')->willReturn(self::SENDER_NAME);
        $this->notificationMock->expects($this->once())->method('getTitle')->willReturn(self::TITLE);
        $this->notificationMock->expects($this->once())->method('getMessage')->willReturn(self::MESSAGE);
        $this->platformMock->expects($this->exactly(2))->method('getNowExpression')->willReturn(self::NOW_EXPRESSION);

        $this->subject->sendNotification($this->notificationMock);
    }

    public function testNotificationCount(): void
    {
        $statementMock = $this->createMock(Statement::class);
        $this->persistenceMock->expects($this->once())->method('query')->with(
            'SELECT status , COUNT(id) as cpt FROM notifications WHERE recipient = ?   GROUP BY status',
            [self::EXAMPLE_USER_ID]
        )->willReturn($statementMock);

        $statementMock->expects($this->once())->method('fetchAll')->willReturn([
            [
                'cpt' => 0,
                'status' => 1,
            ],
            [
                'cpt' => self::NOTIFICATION_COUNT,
                'status' => 2,
            ],
        ]);

        $result = $this->subject->notificationCount(self::EXAMPLE_USER_ID);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame(self::NOTIFICATION_COUNT, $result[2]);
    }

    public function testGetNotifications(): void
    {
        $statementMock = $this->createMock(Statement::class);
        $this->persistenceMock->expects($this->once())->method('query')->with(
            'SELECT id , recipient , status , sender_id , sender_name , title , message , created_at , '
                . 'updated_at FROM notifications WHERE recipient = ? ORDER BY created_at DESC LIMIT 20',
            [self::EXAMPLE_USER_ID]
        )->willReturn($statementMock);

        $statementMock->method('fetchAll')->willReturn([
            [
                'recipient' => self::RECIPIENT_EXAMPLE_1,
                'title' => self::TITLE_EXAMPLE_1,
                'status' => self::STATUS_EXAMPLE_1,
                'sender_id' => self::SENDER_ID_EXAMPLE_1,
                'sender_name' => self::SENDER_NAME_EXAMPLE_1,
                'message' => self::MESSAGE_EXAMPLE_1,
                'created_at' => self::CREATED_AT_EXAMPLE_1,
                'updated_at' => self::UPDATED_AT_EXAMPLE_1,
                'id' => self::NOTIFICATION_ID_EXAMPLE_1
            ],
            [
                'recipient' => self::RECIPIENT_EXAMPLE_2,
                'title' => self::TITLE_EXAMPLE_2,
                'status' => self::STATUS_EXAMPLE_2,
                'sender_id' => self::SENDER_ID_EXAMPLE_2,
                'sender_name' => self::SENDER_NAME_EXAMPLE_2,
                'message' => self::MESSAGE_EXAMPLE_2,
                'created_at' => self::CREATED_AT_EXAMPLE_2,
                'updated_at' => self::UPDATED_AT_EXAMPLE_2,
                'id' => self::NOTIFICATION_ID_EXAMPLE_2
            ],
        ]);

        $result = $this->subject->getNotifications(self::EXAMPLE_USER_ID);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Notification::class, $result[0]);
        $this->assertSame(self::TITLE_EXAMPLE_2, $result[1]->getTitle());
    }

    public function testGetNotification(): void
    {
        $statementMock = $this->createMock(Statement::class);
        $this->persistenceMock->expects($this->once())->method('query')->with(
            'SELECT id , recipient , status , sender_id , sender_name , title , message , created_at , '
                . 'updated_at FROM notifications WHERE id = ? ',
            [self::EXAMPLE_USER_ID]
        )->willReturn($statementMock);

        $statementMock->expects($this->once())->method('fetch')->willReturn([
            'recipient' => self::RECIPIENT_EXAMPLE_1,
            'title' => self::TITLE_EXAMPLE_1,
            'status' => self::STATUS_EXAMPLE_1,
            'sender_id' => self::SENDER_ID_EXAMPLE_1,
            'sender_name' => self::SENDER_NAME_EXAMPLE_1,
            'message' => self::MESSAGE_EXAMPLE_1,
            'created_at' => self::CREATED_AT_EXAMPLE_1,
            'updated_at' => self::UPDATED_AT_EXAMPLE_1,
            'id' => self::NOTIFICATION_ID_EXAMPLE_1
        ]);

        $result = $this->subject->getNotification(self::EXAMPLE_USER_ID);
        $this->assertInstanceOf(Notification::class, $result);
        $this->assertSame(self::TITLE_EXAMPLE_1, $result->getTitle());
        $this->assertSame(self::SENDER_ID_EXAMPLE_1, $result->getSenderId());
        $this->assertSame(self::MESSAGE_EXAMPLE_1, $result->getMessage());
    }
}
