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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\accessControl;

use oat\tao\model\accessControl\RoleBasedContextRestrictAccess;
use PHPUnit\Framework\TestCase;

class RoleBasedContextRestrictAccessTest extends TestCase
{
    public function testIsRestrictedReturnsTrueWhenRoleIsRestricted(): void
    {
        $restrictedRoles = ['admin' => ['role1', 'role2']];
        $roles = ['role1'];
        $restrictedRolesDatasetName = 'admin';

        $roleBasedContextRestrictAccess = new RoleBasedContextRestrictAccess($restrictedRoles);

        $this->assertTrue($roleBasedContextRestrictAccess->isRestricted($roles, $restrictedRolesDatasetName));
    }

    public function testIsRestrictedReturnsFalseWhenRoleIsNotRestricted(): void
    {
        $restrictedRoles = ['admin' => ['role1', 'role2']];
        $roles = ['role3'];
        $restrictedRolesDatasetName = 'admin';

        $roleBasedContextRestrictAccess = new RoleBasedContextRestrictAccess($restrictedRoles);

        $this->assertFalse($roleBasedContextRestrictAccess->isRestricted($roles, $restrictedRolesDatasetName));
    }

    public function testIsRestrictedReturnsFalseWhenRestrictedRolesDatasetNameDoesNotExist(): void
    {
        $restrictedRoles = ['admin' => ['role1', 'role2']];
        $roles = ['role1'];
        $restrictedRolesDatasetName = 'nonexistent';

        $roleBasedContextRestrictAccess = new RoleBasedContextRestrictAccess($restrictedRoles);

        $this->assertFalse($roleBasedContextRestrictAccess->isRestricted($roles, $restrictedRolesDatasetName));
    }
}
