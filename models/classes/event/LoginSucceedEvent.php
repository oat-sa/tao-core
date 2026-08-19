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
 * Copyright (c) 2015-2026 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\tao\model\event;

use JsonSerializable;
use oat\oatbox\event\Event;
use oat\tao\model\session\source\SessionSource;

class LoginSucceedEvent implements Event, JsonSerializable
{
    private readonly int $time;

    public function __construct(
        private readonly string $login = '',
        private readonly string $source = SessionSource::INTERNAL_BACKOFFICE->value
    ) {
        $this->time = time();
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getName(): string
    {
        return __CLASS__;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function jsonSerialize(): array
    {
        return [
            'login' => $this->login,
            'source' => $this->source,
        ];
    }
}
