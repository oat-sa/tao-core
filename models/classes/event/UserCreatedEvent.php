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

use core_kernel_classes_Resource;
use JsonSerializable;
use oat\oatbox\event\Event;
use oat\tao\model\webhooks\WebhookSerializableEventInterface;

class UserCreatedEvent implements Event, JsonSerializable, WebhookSerializableEventInterface
{
    private const WEBHOOK_EVENT_NAME = 'user-created';

    /** @var  string */
    protected $user;

    /**
     * @param core_kernel_classes_Resource $user
     */
    public function __construct(core_kernel_classes_Resource $user)
    {
        $this->user = $user;
    }


    /**
     * Return a unique name for this event
     * @see \oat\oatbox\event\Event::getName()
     */
    public function getName()
    {
        return get_class($this);
    }

    public function jsonSerialize(): array
    {
        return [
            'uri' => $this->user->getUri(),
        ];
    }

    public function getWebhookEventName()
    {
        return self::WEBHOOK_EVENT_NAME;
    }

    public function serializeForWebhook()
    {
        return [
            'userId' => $this->user->getUri(),
        ];
    }
}
