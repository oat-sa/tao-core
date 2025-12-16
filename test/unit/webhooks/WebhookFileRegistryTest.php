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
 * Copyright (c) 2019-2025 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\webhooks;

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\webhooks\configEntity\Webhook;
use oat\tao\model\webhooks\configEntity\WebhookAuth;
use oat\tao\model\webhooks\configEntity\WebhookEntryFactory;
use oat\tao\model\webhooks\WebhookFileRegistry;
use PHPUnit\Framework\MockObject\MockObject;

class WebhookFileRegistryTest extends TestCase
{
    use ServiceManagerMockTrait;

    private WebhookFileRegistry $registry;
    private WebhookEntryFactory|MockObject $webhookEntryFactoryMock;

    protected function setUp(): void
    {
        $this->webhookEntryFactoryMock = $this->createMock(WebhookEntryFactory::class);
    }

    public function testGetWebhookConfig()
    {
        $this->registry = new WebhookFileRegistry([
            WebhookFileRegistry::OPTION_EVENTS => [
                'TestEvent' => ['wh1']
            ],
            WebhookFileRegistry::OPTION_WEBHOOKS => [
                'wh1' => [
                    'id' => 'wh1',
                    'url' => 'http://url.com',
                    'httpMethod' => 'POST',
                    'auth' => [
                        'authClass' => 'SomeClass',
                        'properties' => [
                            'p1' => 'v1'
                        ]
                    ]
                ]
            ]
        ]);

        $serviceLocator = $this->getServiceManagerMock([
            WebhookEntryFactory::class => $this->webhookEntryFactoryMock
        ]);

        $this->registry->setServiceLocator($serviceLocator);
        $returnValue = new Webhook(
            'wh1',
            'http://url.com',
            'POST',
            5,
            new WebhookAuth('SomeClass', ['p1' => 'v1']),
            true
        );

        $this->webhookEntryFactoryMock->expects($this->once())
            ->method('createEntryFromArray')
            ->with([
                'id' => 'wh1',
                'url' => 'http://url.com',
                'httpMethod' => 'POST',
                'auth' => [
                    'authClass' => 'SomeClass',
                    'properties' => [
                        'p1' => 'v1'
                    ]
                ]
            ])
            ->willReturn($returnValue);

        $whConfig = $this->registry->getWebhookConfig('wh1');
        $this->assertSame($returnValue, $whConfig);

        $this->assertNull($this->registry->getWebhookConfig('wh2'));
    }

    public function testGetWebhookConfigIds()
    {
        $this->registry = new WebhookFileRegistry([
            WebhookFileRegistry::OPTION_EVENTS => [
                'TestEvent' => ['wh1']
            ],
            WebhookFileRegistry::OPTION_WEBHOOKS => ['wh1' => []]
        ]);

        $result = $this->registry->getWebhookConfigIds('TestEvent');
        $this->assertEquals(['wh1'], $result);
        $this->assertEquals([], $this->registry->getWebhookConfigIds('AnotherEvent'));
    }

    public function testGetUnexistingWebhookConfigIds()
    {
        $this->registry = new WebhookFileRegistry([
            WebhookFileRegistry::OPTION_EVENTS => [
                'TestEvent' => ['wh1']
            ],
            WebhookFileRegistry::OPTION_WEBHOOKS => ['wh1' => []]
        ]);

        $result = $this->registry->getWebhookConfigIds('AnotherEvent');
        $this->assertEquals([], $result);
    }
}
