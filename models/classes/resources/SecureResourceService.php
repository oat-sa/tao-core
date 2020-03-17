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
 * Copyright (c) 2013-2020   (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\resources;

use common_exception_Error;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\data\permission\PermissionInterface;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;

class SecureResourceService extends ConfigurableService
{
    public const SERVICE_ID = 'tao/SecureResourceService';

    /** @var User */
    private $user;

    /**
     * @param core_kernel_classes_Class $resource
     *
     * @return core_kernel_classes_Resource[]
     * @throws common_exception_Error
     */
    public function getAllChildren(core_kernel_classes_Class $resource)
    {
        $children = $resource->getInstances(true);
        $permissionService = $this->getPermissionProvider();

        $childrenIds = array_map(
            static function (core_kernel_classes_Resource $child) {
                return $child->getUri();
            },
            $children
        );

        $permissions = $permissionService->getPermissions(
            $this->getUser(),
            $childrenIds
        );

        return array_filter(
            $children,
            static function (core_kernel_classes_Resource $child) use ($permissions) {
                $uri = $child->getUri();

                return
                    $permissions[$uri] === [PermissionInterface::RIGHT_UNSUPPORTED]
                    || in_array('READ', $permissions[$uri], true);
            }
        );
    }

    /**
     * @param string[] $resourceUris
     * @param string[] $permissionsToCheck
     *
     * @throws common_exception_Error
     */
    public function validatePermissions(array $resourceUris, array $permissionsToCheck): void
    {
        $permissionService = $this->getPermissionProvider();

        $permissions = $permissionService->getPermissions(
            $this->getUser(),
            $resourceUris
        );

        foreach ($permissions as $key => $permission) {
            if (
                empty($permission)
                || (
                    $permission !== [PermissionInterface::RIGHT_UNSUPPORTED]
                    && !empty(array_diff($permissionsToCheck, $permission))
                )
            ) {
                throw new ResourceAccessDeniedException(
                    sprintf('Access to resource %s is forbidden', $key)
                );
            }
        }
    }

    private function getPermissionProvider(): PermissionInterface
    {
        return $this->getServiceLocator()->get(PermissionInterface::SERVICE_ID);
    }

    /**
     * @return User
     *
     * @throws common_exception_Error
     */
    private function getUser(): User
    {
        if ($this->user === null) {
            $this->user = $this
                ->getServiceLocator()
                ->get(SessionService::SERVICE_ID)
                ->getCurrentUser();
        }

        return $this->user;
    }
}
