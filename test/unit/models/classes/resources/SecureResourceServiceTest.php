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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\resources;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use core_kernel_users_GenerisUser;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\test\GenerisTestCase;
use oat\generis\test\MockObject;
use oat\oatbox\session\SessionService;

class SecureResourceServiceTest extends GenerisTestCase
{
    public function testGetAllChildren(): void
    {
        $service = new SecureResourceService();

        /** @var core_kernel_classes_Class|MockObject $class */
        $class = $this->createMock(core_kernel_classes_Class::class);
        $class->expects($this->once())->method('getInstances')->willReturn(
            $this->getChildrenResources()
        );

        $permissionInterface = $this->createMock(PermissionInterface::class);
        $permissionInterface->method('getPermissions')->willReturn(
            $this->getPermissions()
        );

        $user = $this->createMock(core_kernel_users_GenerisUser::class);
        $sessionService = $this->createMock(SessionService::class);

        $sessionService->expects($this->once())->method('getCurrentUser')->willReturn($user);

        $serviceLocator = $this->getServiceLocatorMock(
            [
                PermissionInterface::SERVICE_ID => $permissionInterface,
                SessionService::SERVICE_ID      => $sessionService,
            ]
        );
        $service->setServiceLocator($serviceLocator);

        $children = $service->getAllChildren($class);

        $this->assertCount(3, $children);
    }

    public function getPermissions(): array
    {
        return [
            'http://resource1' => ['READ'],
            'http://resource2' => [],
            'http://resource3' => ['WRITE'],
            'http://resource4' => ['READ', 'WRITE'],
            'http://resource5' => ['READ', 'WRITE', 'GRANT'],
        ];
    }

    private function getChildrenResources(): array
    {
        $resources = [];
        foreach (array_keys($this->getPermissions()) as $uri) {
            $childResource = $this->createMock(core_kernel_classes_Resource::class);
            $childResource->method('getUri')->willReturn($uri);

            $resources[] = $childResource;
        }

        return $resources;
    }
}
