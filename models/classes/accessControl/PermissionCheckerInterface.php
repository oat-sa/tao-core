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
 * Copyright (c) 2014-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\tao\model\accessControl;

use oat\oatbox\user\User;

interface PermissionCheckerInterface
{
    public const PERMISSION_WRITE = 'WRITE';
    public const PERMISSION_READ = 'READ';
    public const PERMISSION_GRANT = 'GRANT';

    public function hasWriteAccess(string $resourceId, User $user = null): bool;

    public function hasReadAccess(string $resourceId, User $user = null): bool;

    public function hasGrantAccess(string $resourceId, User $user = null): bool;
}
