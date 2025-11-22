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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\event;

use PHPUnit\Framework\TestCase;
use oat\tao\model\event\UserCreatedEvent;
use core_kernel_classes_Resource as RdfResource;

class UserCreatedEventTest extends TestCase
{
    /** @var UserCreatedEvent */
    private $subject;

    /** @var string */
    private $userUriFixture;

    protected function setUp(): void
    {
        $this->userUriFixture = 'test';
        $this->subject = new UserCreatedEvent(
            $this->createConfiguredMock(
                RdfResource::class,
                ['getUri' => $this->userUriFixture]
            )
        );
    }

    public function testSerializeForWebhook(): void
    {
        $this->assertSame(
            [
                'userId' => $this->userUriFixture,
            ],
            $this->subject->serializeForWebhook()
        );
    }

    public function testGetWebhookEventName(): void
    {
        $this->assertSame(
            'user-created',
            $this->subject->getWebhookEventName()
        );
    }
}
