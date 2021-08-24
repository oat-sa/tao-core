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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\media\mapper;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\accessControl\AccessControlEnablerInterface;
use oat\tao\model\accessControl\PermissionChecker;
use oat\tao\model\accessControl\PermissionCheckerInterface;

class MediaBrowserPermissionsMapper extends ConfigurableService implements AccessControlEnablerInterface, MediaBrowserMapperInterface
{
    protected const DATA_PERMISSIONS = 'permissions';

    /** @var PermissionCheckerInterface */
    private $permissionChecker;

    /** @var bool */
    private $enableAccessControl;

    public function enableAccessControl(): AccessControlEnablerInterface
    {
        $this->enableAccessControl = true;

        return $this;
    }

    public function map(array $data, string $resourceUri): array
    {
        $data[self::DATA_PERMISSIONS] = [];

        if ($this->hasReadAccess($resourceUri)) {
            $data[self::DATA_PERMISSIONS][] = PermissionCheckerInterface::PERMISSION_READ;
        }

        if ($this->hasWriteAccess($resourceUri)) {
            $data[self::DATA_PERMISSIONS][] = PermissionCheckerInterface::PERMISSION_WRITE;
        }

        return $data;
    }

    protected function hasReadAccess(string $uri): bool
    {
        return $this->isAccessControlEnabled()
            ? $this->getPermissionChecker()->hasReadAccess($uri)
            : true;
    }

    protected function hasWriteAccess(string $uri): bool
    {
        return $this->isAccessControlEnabled()
            ? $this->getPermissionChecker()->hasWriteAccess($uri)
            : true;
    }

    private function getPermissionChecker(): PermissionCheckerInterface
    {
        if (!$this->permissionChecker) {
            $this->permissionChecker = $this->getServiceLocator()->get(PermissionChecker::class);
        }

        return $this->permissionChecker;
    }

    private function isAccessControlEnabled(): bool
    {
        return $this->enableAccessControl === true;
    }
}
