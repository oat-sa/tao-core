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
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\resources;

use common_cache_Cache;
use common_cache_NotFoundException;
use common_exception_Error;
use core_kernel_classes_Class;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;
use oat\tao\model\resources\GetAllChildrenCacheKeyFactory;
use oat\tao\model\resources\ResourceAccessDeniedException;
use oat\tao\model\resources\SecureResourceCachedService;
use oat\tao\model\resources\SecureResourceService;
use oat\tao\model\resources\SecureResourceServiceAllChildrenCacheCollection;
use oat\tao\model\resources\ValidatePermissionsCacheKeyFactory;
use PHPUnit\Framework\MockObject\MockObject;

class SecureResourceCachedServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    private SecureResourceService|MockObject $service;
    private SecureResourceCachedService $cachedService;
    private common_cache_Cache|MockObject $cache;

    protected function setUp(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getIdentifier')->willReturn('userId');

        $sessionService = $this->createMock(SessionService::class);
        $sessionService->method('getCurrentUser')->willReturn($user);

        $this->service = $this->createMock(SecureResourceService::class);
        $this->cache = $this->createMock(common_cache_Cache::class);

        $sl = $this->getServiceManagerMock(
            [
                SessionService::SERVICE_ID => $sessionService,
                common_cache_Cache::SERVICE_ID => $this->cache,
            ]
        );

        $this->cachedService = new SecureResourceCachedService(
            $this->service,
            new ValidatePermissionsCacheKeyFactory(),
            new GetAllChildrenCacheKeyFactory(),
            common_cache_Cache::SERVICE_ID,
            60
        );

        $this->cachedService->setServiceLocator($sl);
    }

    /**
     * @throws common_cache_NotFoundException
     * @throws common_exception_Error
     */
    public function testValidatePermissionNoDataInCache(): void
    {
        $this->cache->expects($this->once())->method('has');
        $this->cache->expects($this->once())->method('put');
        $this->cachedService->validatePermission('resource', ['READ']);
    }

    /**
     * @throws common_cache_NotFoundException
     * @throws common_exception_Error
     */
    public function testValidatePermissionValidResourceInCache(): void
    {
        $this->cache->expects($this->once())->method('has')->willReturn(true);
        $this->cache->expects($this->once())->method('get')->willReturn(true);
        $this->cache->expects($this->never())->method('put');
        $this->service->expects($this->never())->method('validatePermission');

        $this->cachedService->validatePermission('resource', ['READ']);
    }

    /**
     * @throws common_cache_NotFoundException
     * @throws common_exception_Error
     */
    public function testValidatePermissionNotValidResourceInCache(): void
    {
        $this->expectException(ResourceAccessDeniedException::class);

        $this->cache->expects($this->once())->method('has')->willReturn(true);
        $this->cache->expects($this->once())->method('get')->willReturn(false);
        $this->cache->expects($this->never())->method('put');
        $this->service->expects($this->never())->method('validatePermission');

        $this->cachedService->validatePermission('resource', ['READ']);
    }

    /**
     * @throws common_cache_NotFoundException
     * @throws common_exception_Error
     */
    public function testValidatePermissions(): void
    {
        $this->cache->method('has')->willReturn(false);
        $this->service->expects($this->exactly(3))->method('validatePermission');

        $this->cachedService->validatePermissions(['1','2', '3'], ['READ']);
    }

    /**
     * @throws common_cache_NotFoundException
     * @throws common_exception_Error
     */
    public function testGetAllChildrenDataInCache(): void
    {
        $resultCollection = $this->createMock(SecureResourceServiceAllChildrenCacheCollection::class);

        $this->cache->expects($this->once())->method('has')->willReturn(true);
        $this->cache->expects($this->once())->method('get')->willReturn($resultCollection);
        $this->cache->expects($this->never())->method('put');
        $this->service->expects($this->never())->method('getAllChildren');

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class->method('getUri')->willReturn('userId');

        $this->cachedService->getAllChildren($class);
    }

    /**
     * @throws common_cache_NotFoundException
     * @throws common_exception_Error
     */
    public function testGetAllChildrenDataNotInCache(): void
    {
        $this->cache->expects($this->once())->method('has')->willReturn(false);
        $this->cache->expects($this->never())->method('get');
        $this->cache->expects($this->once())->method('put');
        $this->service->expects($this->once())->method('getAllChildren');

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class->method('getUri')->willReturn('userId');

        $this->cachedService->getAllChildren($class);
    }
}
