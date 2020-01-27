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

use oat\oatbox\service\ConfigurableService;

class JsonWebhookPayloadFactory extends ConfigurableService implements WebhookPayloadFactoryInterface
{
    const SOURCE = 'source';
    const EVENTS = 'events';

    const EVENT_ID = 'eventId';
    const EVENT_NAME = 'eventName';
    const TRIGGERED_TIMESTAMP = 'triggeredTimestamp';
    const EVENT_DATA = 'eventData';

    /**
     * @param string $eventName
     * @param string $eventId
     * @param int $triggeredTimestamp
     * @param array $eventData
     * @return string
     * @throws \common_Exception
     */
    public function createPayload($eventName, $eventId, $triggeredTimestamp, $eventData)
    {
        $data = [
            self::SOURCE => $this->getSourceUrl(),
            self::EVENTS => [
                [
                    self::EVENT_ID => $eventId,
                    self::EVENT_NAME => $eventName,
                    self::TRIGGERED_TIMESTAMP => $triggeredTimestamp,
                    self::EVENT_DATA => $eventData
                ]
            ]
        ];

        $result = json_encode($data);
        if ($result === false) {
            throw new \common_Exception("Can't prepare payload: " . json_last_error_msg());
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return 'application/json';
    }

    /**
     * @return string
     */
    private function getSourceUrl()
    {
        return defined('ROOT_URL')
            ? ROOT_URL
            : gethostname();
    }
}
