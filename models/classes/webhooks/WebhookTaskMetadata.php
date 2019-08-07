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

use ArrayObject;

/**
 * Traversable object with described keys which could be passed to task as metadata
 */
class WebhookTaskMetadata extends ArrayObject
{
    const EVENT_NAME = 'eventName';
    const EVENT_DATA = 'eventData';
    const WEBHOOK_CONFIG_ID = 'webhookConfigId';

    /**
     * @param string $eventName
     * @param array $eventData
     * @param string $webhookConfigId
     */
    public function __construct($eventName, array $eventData, $webhookConfigId)
    {
        parent::__construct([
            self::EVENT_NAME => $eventName,
            self::EVENT_DATA => $eventData,
            self::WEBHOOK_CONFIG_ID => $webhookConfigId
        ]);
    }
}
