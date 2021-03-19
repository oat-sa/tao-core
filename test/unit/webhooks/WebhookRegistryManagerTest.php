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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\webhooks;

use oat\generis\test\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\webhooks\configEntity\Webhook;
use oat\tao\model\webhooks\WebhookFileRegistry;
use oat\tao\model\webhooks\WebhookRegistryManager;
use PHPUnit\Framework\MockObject\MockObject;

class WebhookRegistryManagerTest extends TestCase
{
    /** @var WebhookRegistryManager */
    private $subject;

    /** @var WebhookFileRegistry|MockObject */
    private $webhookFileRegistryMock;

    /** @var Webhook|MockObject */
    private $webhookMock;

    /** @var ServiceManager|MockObject */
    private $serviceManager;

    public function setUp(): void
    {
        $this->webhookMock = $this->createMock(Webhook::class);
        $this->webhookFileRegistryMock = $this->createMock(WebhookFileRegistry::class);
        $this->serviceManager = $this->createMock(ServiceManager::class);
        $this->serviceManager
            ->method('get')
            ->with(WebhookFileRegistry::class)
            ->willReturn($this->webhookFileRegistryMock);

        $this->subject = new WebhookRegistryManager();
        $this->subject->setServiceLocator(
            $this->serviceManager
        );
    }

    public function testAddWebhookConfig(): void
    {
        $this->webhookFileRegistryMock
            ->expects($this->exactly(2))
            ->method('getOption')
            ->withConsecutive(
                [
                    'webhooks',
                ],
                [
                    'events',
                ]
            )
            ->willReturnOnConsecutiveCalls(
                [],
                []
            );

        $this->webhookMock
            ->expects($this->exactly(2))
            ->method('getid')
            ->willReturn('webhookId');

        $this->webhookMock
            ->expects($this->once())
            ->method('toArray')
            ->willReturn(
                [
                    'id' => 'webhookId',
                ]
            );

        $this->webhookFileRegistryMock
            ->expects($this->exactly(2))
            ->method('setOption')
            ->withConsecutive(
                ['webhooks', [
                    'webhookId' => [
                        'id' => 'webhookId',
                    ]
                ]],
                ['events', [
                    'SomeEventClass' => [
                        'webhookId'
                    ]
                ]]
            );

        $this->serviceManager
            ->expects($this->once())
            ->method('register')
            ->with(
                WebhookFileRegistry::SERVICE_ID,
                $this->webhookFileRegistryMock
            );

        $this->subject->addWebhookConfig($this->webhookMock, 'SomeEventClass');
    }
}
