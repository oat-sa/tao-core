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

class SecureResourceService extends ConfigurableService implements SecureResourceServiceInterface
{
    private const HIGHEST_PARENT_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#AssessmentContentObject';

    /** @var User */
    private $user;

    /**
     * @param core_kernel_classes_Class $resource
     *
     * @return core_kernel_classes_Resource[]
     * @throws common_exception_Error
     */
    public function getAllChildren(core_kernel_classes_Class $resource): array
    {
        $subClasses = $resource->getSubClasses(false);

        $accessibleInstances = [[]];

        $permissionService = $this->getPermissionProvider();

        if ($subClasses) {
            foreach ($subClasses as $subClass) {
                $classUri = $subClass->getUri();
                $classPermissions = $permissionService->getPermissions($this->getUser(), [$classUri]);

                if ($this->hasAccess($classPermissions[$classUri])) {
                    $accessibleInstances[] = $this->getAllChildren($subClass);
                }
            }
        }

        return array_merge(
            $this->getInstances($resource),
            ...$accessibleInstances
        );
    }

    /**
     * @param core_kernel_classes_Class $class
     *
     * @return core_kernel_classes_Resource[]
     * @throws common_exception_Error
     */
    private function getInstances(core_kernel_classes_Class $class): array
    {
        $instances = $class->getInstances(false);

        if (!$instances) {
            return [];
        }

        $childrenUris = array_map(
            static function (core_kernel_classes_Resource $child) {
                return $child->getUri();
            },
            $instances
        );

        $permissions = $this->getPermissionProvider()->getPermissions(
            $this->getUser(),
            $childrenUris
        );

        $accessibleInstances = [];

        foreach ($instances as $child) {
            $uri = $child->getUri();
            if ($this->hasAccess($permissions[$uri])) {
                $accessibleInstances[$uri] = $child;
            }
        }

        return $accessibleInstances;
    }

    private function hasAccess(array $permissions, array $permissionsToCheck = ['READ']): bool
    {
        return
            $permissions === [PermissionInterface::RIGHT_UNSUPPORTED]
            || empty(array_diff($permissionsToCheck, $permissions));
    }

    /**
     * @param string[] $resourceUris
     * @param string[] $permissionsToCheck
     *
     * @throws common_exception_Error
     */
    private function validateResourceUriPermissions(array $resourceUris, array $permissionsToCheck): void
    {
        $permissionService = $this->getPermissionProvider();

        $permissions = $permissionService->getPermissions(
            $this->getUser(),
            $resourceUris
        );

        foreach ($permissions as $uri => $permission) {
            if (
                empty($permission)
                || !$this->hasAccess($permission, $permissionsToCheck)
            ) {
                throw new ResourceAccessDeniedException($uri);
            }
        }
    }

    /**
     * @param core_kernel_classes_Resource[] $resources
     * @param string[]                       $permissionsToCheck
     *
     * @throws common_exception_Error
     */
    public function validatePermissions(iterable $resources, array $permissionsToCheck): void
    {
        foreach ($resources as $resource) {
            $this->validatePermission($resource, $permissionsToCheck);
        }
    }

    /**
     * @param core_kernel_classes_Resource|string $resource
     * @param array                               $permissionsToCheck
     *
     * @throws common_exception_Error
     */
    public function validatePermission($resource, array $permissionsToCheck): void
    {
        $permissionService = $this->getPermissionProvider();

        if (is_string($resource)) {
            $resource = new core_kernel_classes_Resource($resource);
        }

        $resourceUri = $resource->getUri();
        $permissions = $permissionService->getPermissions($this->getUser(), [$resourceUri]);

        if (!$this->hasAccess($permissions[$resourceUri], $permissionsToCheck)) {
            throw new ResourceAccessDeniedException($resourceUri);
        }

        $parentUris = $this->getParentUris(
            $this->getClass($resource)
        );

        $this->validateResourceUriPermissions($parentUris, $permissionsToCheck);
    }

    private function getClass(core_kernel_classes_Resource $resource): core_kernel_classes_Class
    {
        if ($resource instanceof core_kernel_classes_Class) {
            return $resource;
        }

        // fetch parent class
        if (!$resource->isClass()) {
            return current($resource->getTypes());
        }

        // the last chance to fetch class form DB
        return $resource->getClass($resource->getUri());
    }

    private function getParentUris(core_kernel_classes_Class $parent): array
    {
        $parentUris = [$parent->getUri()];

        while ($parentList = $parent->getParentClasses(false)) {
            $parent = current($parentList);
            if ($parent->getUri() === self::HIGHEST_PARENT_URI) {
                break;
            }
            $parentUris[] = $parent->getUri();
        }

        return $parentUris;
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
