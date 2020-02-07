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

namespace oat\tao\model\webhooks\task;

use ArrayObject;

/**
 * Traversable object with described keys which could be passed to task as metadata
 */
class WebhookTaskParams extends ArrayObject
{
    const EVENT_NAME = 'eventName';
    const EVENT_ID = 'eventId';
    const TRIGGERED_TIMESTAMP = 'triggeredTimestamp';
    const EVENT_DATA = 'eventData';
    const WEBHOOK_CONFIG_ID = 'webhookConfigId';
    const RETRY_MAX = 'retryMax';
    const RETRY_COUNT = 'retryCount';
    const RESPONSE_VALIDATION = 'responseValidation';

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this[self::EVENT_NAME];
    }

    /**
     * @return string
     */
    public function getEventId()
    {
        return $this[self::EVENT_ID];
    }

    /**
     * @return int
     */
    public function getTriggeredTimestamp()
    {
        return $this[self::TRIGGERED_TIMESTAMP];
    }

    /**
     * @return array
     */
    public function getEventData()
    {
        return $this[self::EVENT_DATA];
    }

    /**
     * @return string
     */
    public function getWebhookConfigId()
    {
        return $this[self::WEBHOOK_CONFIG_ID];
    }

    /**
     * @return int
     */
    public function getRetryMax()
    {
        return $this[self::RETRY_MAX];
    }

    /**
     * increase retry counter
     */
    public function increaseRetryCount()
    {
        $this[self::RETRY_COUNT]++;
    }

    /**
     * Check if retry counter reached max retry value
     * @return bool
     */
    public function isMaxRetryCountReached()
    {
        return $this[self::RETRY_COUNT] >= $this[self::RETRY_MAX];
    }

    public function getRetryCount()
    {
        return $this[self::RETRY_COUNT];
    }

    public function responseValidation()
    {
        return $this[self::RESPONSE_VALIDATION];
    }
}
