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
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\notification;

use PHPUnit\Framework\TestCase;
use oat\tao\model\notification\Notification;

class NotificationTest extends TestCase
{
    private const USER_ID = 'user_id';
    private const TITLE = 'title';
    private const MESSAGE = 'long message';
    private const SENDER_ID = 'sender_id';
    private const SENDER_NAME = 'sender name';
    private const NOTIFICATION_ID = 'notification_id';
    private const UPDATED_NOTIFICATION_ID = 'updated_notification_id';
    private const CREATED_AT = '2000-01-02 23:59:59';
    private const UPDTAED_AT = '2001-02-03 01:00:00';
    private const STATUS = 1;

    private Notification $subjectCompleted;

    protected function setUp(): void
    {
        $this->subjectCompleted = new Notification(
            self::USER_ID,
            self::TITLE,
            self::MESSAGE,
            self::SENDER_ID,
            self::SENDER_NAME,
            self::NOTIFICATION_ID,
            self::CREATED_AT,
            self::UPDTAED_AT,
            self::STATUS
        );
    }

    public function testGetStatus(): void
    {
        $result = $this->subjectCompleted->getStatus();
        $this->assertEquals(self::STATUS, $result);
    }

    public function testGetRecipient(): void
    {
        $result = $this->subjectCompleted->getRecipient();
        $this->assertEquals(self::USER_ID, $result);
    }

    public function testGetSenderId(): void
    {
        $result = $this->subjectCompleted->getSenderId();
        $this->assertEquals(self::SENDER_ID, $result);
    }

    public function testGetSenderName(): void
    {
        $result = $this->subjectCompleted->getSenderName();
        $this->assertEquals(self::SENDER_NAME, $result);
    }

    public function testGetMessage(): void
    {
        $result = $this->subjectCompleted->getMessage();
        $this->assertEquals(self::MESSAGE, $result);
    }

    public function testGetId(): void
    {
        $result = $this->subjectCompleted->getId();
        $this->assertEquals(self::NOTIFICATION_ID, $result);
    }

    public function testGetCreatedAt(): void
    {
        $result = $this->subjectCompleted->getCreatedAt();
        $this->assertEquals(946857599, $result);
    }

    public function testGetUpdatedAt(): void
    {
        $result = $this->subjectCompleted->getUpdatedAt();
        $this->assertEquals(981162000, $result);
    }

    public function testSetStatus(): void
    {
        $result = $this->subjectCompleted->setStatus(2);
        $this->assertEquals(2, $result->getStatus());
        $this->assertNotEquals(self::UPDTAED_AT, $result->getUpdatedAt());
    }

    public function testSetId(): void
    {
        $result = $this->subjectCompleted->setId(self::UPDATED_NOTIFICATION_ID);
        $this->assertInstanceOf(Notification::class, $result);
        $this->assertEquals(self::NOTIFICATION_ID, $result->getId());
    }

    public function testSetIdWhenEmpty(): void
    {
        $subject = new Notification(
            self::USER_ID,
            self::TITLE,
            self::MESSAGE,
            self::SENDER_ID,
            self::SENDER_NAME
        );

        $result = $subject->setId(self::UPDATED_NOTIFICATION_ID);
        $this->assertEquals(self::UPDATED_NOTIFICATION_ID, $result->getId());
    }

    public function testGetTitle(): void
    {
        $result = $this->subjectCompleted->getTitle();
        $this->assertEquals(self::TITLE, $result);
    }

    public function testJsonSerialize(): void
    {
        $result = $this->subjectCompleted->jsonSerialize();
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals(self::NOTIFICATION_ID, $result['id']);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals(self::STATUS, $result['status']);
        $this->assertArrayHasKey('recipient', $result);
        $this->assertEquals(self::USER_ID, $result['recipient']);
        $this->assertArrayHasKey('sender', $result);
        $this->assertEquals(self::SENDER_ID, $result['sender']);
        $this->assertArrayHasKey('senderName', $result);
        $this->assertEquals(self::SENDER_ID, $result['senderName']);
        $this->assertArrayHasKey('title', $result);
        $this->assertEquals(self::TITLE, $result['title']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(self::MESSAGE, $result['message']);
        $this->assertArrayHasKey('createdAt', $result);
        $this->assertEquals(946857599, $result['createdAt']);
        $this->assertArrayHasKey('updatedAt', $result);
        $this->assertEquals(981162000, $result['updatedAt']);
    }
}
