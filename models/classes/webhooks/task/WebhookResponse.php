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

class WebhookResponse
{
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_IGNORED = 'ignored';
    const STATUS_ERROR = 'error';

    /**
     * Key is event id, value - one of the statuses
     * @var string[]
     */
    private $statuses;

    /**
     * @var string|null
     */
    private $parseError;

    /**
     * @param string[] $statuses
     * @param string|null $parseError
     */
    public function __construct(
        array $statuses = [],
        $parseError = null
    ) {
        $this->statuses = $statuses;
        $this->parseError = $parseError;
    }

    /**
     * @return string[]
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * @param $eventId
     * @return string|null
     */
    public function getStatus($eventId)
    {
        return isset($this->statuses[$eventId])
            ? $this->statuses[$eventId]
            : null;
    }

    /**
     * @param string $eventId
     * @return bool
     */
    public function isDelivered($eventId)
    {
        return in_array($this->getStatus($eventId), [self::STATUS_ACCEPTED, self::STATUS_IGNORED], true);
    }

    /**
     * @return string|null
     */
    public function getParseError()
    {
        return $this->parseError;
    }
}
