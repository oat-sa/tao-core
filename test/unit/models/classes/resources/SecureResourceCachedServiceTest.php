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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\test\unit\model\resources;

use common_cache_Cache;
use common_persistence_Manager;
use oat\generis\test\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;
use oat\tao\model\resources\ResourceAccessDeniedException;
use oat\tao\model\resources\SecureResourceCachedService;
use oat\tao\model\resources\SecureResourceService;
use oat\tao\model\resources\ValidatePermissionsCacheKeyFactory;
use PHPUnit\Framework\MockObject\MockObject;

class SecureResourceCachedServiceTest extends TestCase
{
    /**
     * @var SecureResourceService|MockObject
     */
    private $service;
    /**
     * @var SecureResourceCachedService
     */
    private $cachedService;
    /**
     * @var common_cache_Cache|MockObject
     */
    private $cache;

    protected function setUp(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getIdentifier')->willReturn('userId');

        $sessionService = $this->createMock(SessionService::class);
        $sessionService->method('getCurrentUser')->willReturn($user);

        $validateKey = $this->createMock(ValidatePermissionsCacheKeyFactory::class);

        $this->service = $this->createMock(SecureResourceService::class);
        $this->cache = $this->createMock(common_cache_Cache::class);

        $sl = $this->getServiceLocatorMock(
            [
                SessionService::SERVICE_ID                => $sessionService,
                ValidatePermissionsCacheKeyFactory::class => $validateKey,
                common_cache_Cache::SERVICE_ID            => $this->cache,
            ]
        );

        $this->cachedService = new SecureResourceCachedService($this->service, common_cache_Cache::SERVICE_ID, 60);
        $this->cachedService->setServiceLocator($sl);
    }


    public function testValidatePermissionNoDataInCache(): void
    {
        $this->cache->expects($this->once())->method('has');
        $this->cache->expects($this->once())->method('put');
        $this->cachedService->validatePermission('resource', ['READ']);
    }

    public function testValidatePermissionValidResourceInCache(): void
    {
        $this->cache->expects($this->once())->method('has')->willReturn(true);
        $this->cache->expects($this->once())->method('get')->willReturn(true);
        $this->cache->expects($this->never())->method('put');
        $this->service->expects($this->never())->method('validatePermission');

        $this->cachedService->validatePermission('resource', ['READ']);
    }

    public function testValidatePermissionNotValidResourceInCache(): void
    {
        $this->expectException(ResourceAccessDeniedException::class);

        $this->cache->expects($this->once())->method('has')->willReturn(true);
        $this->cache->expects($this->once())->method('get')->willReturn(false);
        $this->cache->expects($this->never())->method('put');
        $this->service->expects($this->never())->method('validatePermission');

        $this->cachedService->validatePermission('resource', ['READ']);
    }

    public function testValidatePermissions(): void
    {

    }

    public function testGetAllChildren(): void
    {

    }
}
