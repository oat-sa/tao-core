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
 *
 */

namespace oat\tao\model\oauth\lockout\storage;

interface LockoutStorageInterface
{
    public const FIELD_IP_ADDRESS = 'ip_address';
    public const FIELD_REASON = 'reason';
    public const FIELD_EXPIRE_AT = 'expire_at';
    public const FIELD_ATTEMPT = 'attempt';
    public const FIELD_CREDENTIALS_HASH = '';

    public function store();

    public function isExpired();
    public function getFailedAttempts();
    public function getLatestReason();

//    public function getLocksForCreds(); // ??


}
