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

use oat\oatbox\event\Event;

interface WebhookEventsServiceInterface
{
    const SERVICE_ID = 'tao/webhookEventsService';

    /**
     * Save new supported event to service config and attach listener to eventManager
     * Don't forget to re-register EventManager after this call!
     *
     * @param string $eventName
     */
    public function registerEvent($eventName);

    /**
     * Remove event from list of supported events and detach listener in eventManager
     * Don't forget to re-register EventManager after this call!
     *
     * @param string $eventName
     */
    public function unregisterEvent($eventName);

    /**
     * @param string $eventName
     * @return bool
     */
    public function isEventRegistered($eventName);

    /**
     * @return string[]
     */
    public function getRegisteredEvents();

    public function handleEvent(Event $event);
}
