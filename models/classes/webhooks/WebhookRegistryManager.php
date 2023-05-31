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

namespace oat\tao\model\webhooks;

use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Laminas\ServiceManager\ServiceLocatorAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\webhooks\configEntity\Webhook;

class WebhookRegistryManager extends ConfigurableService implements
    WebhookRegistryManagerInterface,
    ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function addWebhookConfig(Webhook $webhook, string $event): void
    {
        $webhookRegistry = $this->getWebhookRegistry();

        $webhookRegistry->addWebhook($webhook, [$event]);
    }

    private function getWebhookRegistry(): WebhookRegistryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->getContainer()->get(WebhookRegistryInterface::class);
    }
}
