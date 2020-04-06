<?php

declare(strict_types=1);

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
 *
 */

namespace oat\tao\model\oauth\lockout\storage;

use Exception;

/**
 * Describe how should be implemented storage of failed OAuth sessions
 *
 * Interface LockoutStorageInterface
 * @package oat\tao\model\oauth\lockout\storage
 */
interface LockoutStorageInterface
{
    public const OPTION_PERSISTENCE = 'persistence';

    /**
     * Saves the data into storage
     *
     * @param string $ip Client IP address
     * @param int $ttl How long entry will be valid
     *
     * @throws Exception
     */
    public function store(string $ip, int $ttl = 0): void;

    /**
     * Returns amount of failed attempts for requested IP
     *
     * @param string $ip Client IP Address
     *
     * @param int $timeout
     * @return mixed
     */
    public function getFailedAttempts(string $ip, int $timeout): int;

}
