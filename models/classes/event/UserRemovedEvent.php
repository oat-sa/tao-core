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
 * Copyright (c) 2015-2020 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\model\event;

use JsonSerializable;
use oat\oatbox\event\Event;
use oat\tao\model\webhooks\WebhookSerializableEventInterface;

class UserRemovedEvent implements Event, JsonSerializable, WebhookSerializableEventInterface
{
    public const EVENT_NAME = __CLASS__;

    private const WEBHOOK_EVENT_NAME = 'user-removed';

    /** @var  string */
    private $userUri;

    /**
     * @param string $userUri
     */
    public function __construct($userUri)
    {
        $this->userUri = $userUri;
    }


    /**
     * Return a unique name for this event
     * @see \oat\oatbox\event\Event::getName()
     */
    public function getName()
    {
        return self::EVENT_NAME;
    }

    public function jsonSerialize(): array
    {
        return [
            'uri' => $this->userUri,
        ];
    }

    public function getWebhookEventName()
    {
        return self::WEBHOOK_EVENT_NAME;
    }

    public function serializeForWebhook()
    {
        return [
            'userId' => $this->userUri,
        ];
    }
}
