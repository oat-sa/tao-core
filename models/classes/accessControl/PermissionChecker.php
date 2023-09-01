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

use common_session_SessionManager;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\tao\model\accessControl\data\DataAccessControl;

class PermissionChecker extends ConfigurableService implements PermissionCheckerInterface
{
    /** @var AccessControl */
    private $dataAccessControl;

    public function withAccessControl(AccessControl $dataAccessControl): self
    {
        $this->dataAccessControl = $dataAccessControl;

        return $this;
    }

    public function hasWriteAccess(string $resourceId, User $user = null): bool
    {
        return $this->hasAccess($resourceId, self::PERMISSION_WRITE, $user);
    }

    public function hasReadAccess(string $resourceId, User $user = null): bool
    {
        return $this->hasAccess($resourceId, self::PERMISSION_READ, $user);
    }

    public function hasGrantAccess(string $resourceId, User $user = null): bool
    {
        return $this->hasAccess($resourceId, self::PERMISSION_GRANT, $user);
    }

    private function hasAccess(string $resourceId, string $access, User $user = null): bool
    {
        return $this->getAccessControl()->hasPrivileges(
            $user ?? $this->getUser(),
            [
                $resourceId => $access
            ]
        );
    }

    private function getUser(): User
    {
        return common_session_SessionManager::getSession()->getUser();
    }

    private function getAccessControl(): AccessControl
    {
        if (!$this->dataAccessControl) {
            $this->dataAccessControl = new DataAccessControl();
        }

        return $this->dataAccessControl;
    }
}
