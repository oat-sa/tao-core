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

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\webhooks\configEntity\Webhook;
use oat\tao\model\webhooks\WebhookFileRegistry;
use oat\tao\model\webhooks\WebhookRegistryInterface;
use oat\tao\model\webhooks\WebhookRegistryManager;
use PHPUnit\Framework\MockObject\MockObject;

class WebhookRegistryManagerTest extends TestCase
{
    use ServiceManagerMockTrait;

    private WebhookRegistryManager $subject;
    private WebhookFileRegistry|MockObject $webhookFileRegistryMock;
    private Webhook|MockObject $webhookMock;

    protected function setUp(): void
    {
        $this->webhookMock = $this->createMock(Webhook::class);
        $this->webhookFileRegistryMock = $this->createMock(WebhookFileRegistry::class);

        $this->subject = new WebhookRegistryManager();
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock([
                WebhookRegistryInterface::class => $this->webhookFileRegistryMock
            ])
        );
    }

    public function testAddWebhookConfig(): void
    {
        $this->webhookFileRegistryMock
            ->expects($this->once())
            ->method('addWebhook');

        $this->subject->addWebhookConfig($this->webhookMock, 'SomeEventClass');
    }
}
