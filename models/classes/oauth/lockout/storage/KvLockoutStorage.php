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
 * Copyright (c) 2020 (original work) (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT)
 */

declare(strict_types=1);

namespace oat\tao\model\oauth\lockout\storage;

use common_persistence_KeyValuePersistence as Persistence;

/**
 * @method Persistence getPersistence()
 */
class KvLockoutStorage extends LockoutStorageAbstract
{
    /**
     * @inheritDoc
     */
    public function store(string $ip, int $ttl = 0): void
    {
        $this->getPersistence()->set(
            $this->createKey($ip),
            $this->getFailedAttempts($ip, $ttl) + 1,
            $ttl
        );
    }

    /**
     * @inheritDoc
     */
    public function getFailedAttempts(string $ip, int $timeout): int
    {
        return (int)$this->getPersistence()->get(
            $this->createKey($ip)
        );
    }

    private function createKey(string $ip): string
    {
        return sprintf('%s_%u', self::class, ip2long($ip));
    }
}
