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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\webhooks;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\webhooks\configEntity\Webhook;
use oat\tao\model\webhooks\configEntity\WebhookEntryFactory;
use oat\tao\model\webhooks\task\WebhookTaskParams;

/**
 * Implementation which uses own (service) configuration to store webhooks configuration
 *
 * Options:
 * `events` is array with:
 *      key: name of event
 *      value: array of string ids of connected webhooks
 *
 * `webhooks` is array with:
 *      key: webhook unique id
 *      value: array representation of ConfigEntry\Webhook
 *
 * See tao/test/unit/webhooks/WebhookConfigFileRepositoryTest.php
 * for example of configuration
 */
class WebhookFileRegistry extends ConfigurableService implements WebhookRegistryInterface
{
    const OPTION_WEBHOOKS = 'webhooks';
    const OPTION_EVENTS = 'events';

    /**
     * @param string $id
     * @return Webhook|null
     */
    public function getWebhookConfig($id)
    {
        $webhooks = $this->getOption(self::OPTION_WEBHOOKS);
        if (!isset($webhooks[$id])) {
            return null;
        }

        try {
            return $this->getWebhookEntryFactory()->createEntryFromArray($webhooks[$id]);
        } catch (\InvalidArgumentException $exception) {
            throw new \InvalidArgumentException("Invalid '$id' webhook config. " . $exception->getMessage());
        }
    }

    /**
     * @param string $eventName
     * @return string[]
     */
    public function getWebhookConfigIds($eventName)
    {
        $events = $this->getOption(self::OPTION_EVENTS);

        return isset($events[$eventName])
            ? $events[$eventName]
            : [];
    }

    /**
     * @return WebhookEntryFactory
     */
    private function getWebhookEntryFactory()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookEntryFactory::class);
    }
}
