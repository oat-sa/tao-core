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
use oat\tao\model\webhooks\ConfigEntity\Webhook;

/**
 * Implementation which uses own (service) configuration to store webhooks configuration
 */
class WebhookConfigFileRepository extends ConfigurableService implements WebhookConfigRepositoryInterface
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

        return Webhook::fromArray($webhooks[$id]);
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
}
