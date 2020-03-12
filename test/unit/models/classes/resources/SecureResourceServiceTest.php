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

use common_exception_Error;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use core_kernel_users_GenerisUser;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\test\GenerisTestCase;
use oat\generis\test\MockObject;
use oat\oatbox\session\SessionService;

class SecureResourceServiceTest extends GenerisTestCase
{
    /**
     * @var SecureResourceService
     */
    private $service;
    /**
     * @var PermissionInterface
     */
    private $permissionInterface;

    public function setUp()
    {
        $this->service = new SecureResourceService();

        $this->permissionInterface = $this->createMock(PermissionInterface::class);

        $user = $this->createMock(core_kernel_users_GenerisUser::class);
        $sessionService = $this->createMock(SessionService::class);

        $sessionService->expects($this->once())->method('getCurrentUser')->willReturn($user);

        $serviceLocator = $this->getServiceLocatorMock(
            [
                PermissionInterface::SERVICE_ID => $this->permissionInterface,
                SessionService::SERVICE_ID      => $sessionService,
            ]
        );

        $this->service->setServiceLocator($serviceLocator);
    }

    /**
     * @throws common_exception_Error
     */
    public function testGetAllChildren(): void
    {
        $this->permissionInterface->method('getPermissions')->willReturn(
            $this->getPermissions()
        );

        /** @var core_kernel_classes_Class|MockObject $class */
        $class = $this->createMock(core_kernel_classes_Class::class);
        $class->expects($this->once())->method('getInstances')->willReturn(
            $this->getChildrenResources()
        );

        $children = $this->service->getAllChildren($class);

        $this->assertCount(3, $children);
    }

    /**
     * @param array $permissions
     * @param array $permissionsToCheck
     * @param bool  $hasAccess
     *
     * @throws common_exception_Error
     * @dataProvider provideResources
     *
     */
    public function testValidatePermissions(array $permissions, array $permissionsToCheck, bool $hasAccess): void
    {
        $this->permissionInterface->method('getPermissions')->willReturn(
            array_intersect_key(
                $this->getPermissions(),
                array_flip($permissions)
            )
        );

        if (!$hasAccess) {
            $this->expectException(ResourceAccessDeniedException::class);
        }

        $this->service->validatePermissions($permissions, $permissionsToCheck);
    }

    public function provideResources(): array
    {
        return [
            [
                [
                    'http://resource2',
                    'http://resource1'
                ],
                ['READ'],
                false
            ],
            [
                [
                    'http://resource4',
                    'http://resource5'
                ],
                ['READ'],
                true
            ],
            [
                [
                    'http://resource4',
                    'http://resource5'
                ],
                ['WRITE', 'READ'],
                true
            ],
            [
                [
                    'http://resource4',
                    'http://resource5'
                ],
                ['GRANT'],
                false
            ],
        ];
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
