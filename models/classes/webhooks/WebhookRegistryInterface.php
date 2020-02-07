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

use oat\tao\model\webhooks\configEntity\WebhookInterface;

/**
 * Represents webhooks config entries storage. We will add 'set' methods here if we are going to implement
 * dynamic configuration for webhooks (admin web panel)
 */
interface WebhookRegistryInterface
{
    const SERVICE_ID = 'tao/webhookRegistry';

    /**
     * @param string $id
     * @return WebhookInterface|null
     */
    public function getWebhookConfig($id);

    /**
     * @param string $eventName
     * @return string[]
     */
    public function getWebhookConfigIds($eventName);
}
