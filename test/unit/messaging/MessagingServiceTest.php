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
 * Copyright (c) 2015-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\messaging;

use oat\tao\model\messaging\MessagingService;
use oat\tao\model\messaging\Message;
use oat\tao\model\messaging\Transport;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

class MessagingServiceTest extends TestCase
{
    private function getMessagingService(Transport $transport): MessagingService
    {
        $messagingService = MessagingService::singleton();
        $refObject = new ReflectionObject($messagingService);
        $refProperty = $refObject->getProperty('transport');
        $refProperty->setAccessible(true);
        $refProperty->setValue($messagingService, $transport);

        return $messagingService;
    }

    public function testSend(): void
    {
        $message = new Message();
        $transportMock = $this->createMock(Transport::class);
        $transportMock
            ->expects($this->once())
            ->method('send')
            ->with($this->identicalTo($message))
            ->willReturn(true);

        $messagingService = $this->getMessagingService($transportMock);

        $result = $messagingService->send($message);

        $this->assertTrue($result);
    }

    public function testIsAvailable(): void
    {
        $transportMock = $this->createMock(Transport::class);
        $messagingService = $this->getMessagingService($transportMock);

        $this->assertTrue($messagingService->isAvailable());
    }
}
