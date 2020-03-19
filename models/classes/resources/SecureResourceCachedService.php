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

use common_cache_Cache;
use common_cache_NotFoundException;
use common_exception_Error;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;
use RuntimeException;

class SecureResourceCachedService extends ConfigurableService implements SecureResourceServiceInterface
{
    public const OPTION_CACHE = 'cache';
    public const OPTION_CACHE_ENABLED = 'enabled';
    public const OPTION_CACHE_TTL = 'ttl';
    public const OPTION_SERVICE = 'service';

    /** @var User */
    private $user;

    /** @var  SecureResourceService */
    private $service;

    /**
     * @param core_kernel_classes_Class $resource
     *
     * @return core_kernel_classes_Resource[]
     * @throws common_exception_Error
     * @throws common_cache_NotFoundException
     */
    public function getAllChildren(core_kernel_classes_Class $resource): array
    {
        $user = $this->getUser();

        $cacheKey = $this->getCacheKeyFactory()->create($resource, $user);

        $cache = $this->getCache();
        if ($cache && $cache->has($cacheKey)) {
            return $cache->get($cacheKey)->getInstances();
        }

        $accessibleInstances = $this->getService()->getAllChildren($resource);

        if ($cache) {
            $cache->put(
                new SecureResourceServiceAllChildrenCacheCollection($accessibleInstances),
                $cacheKey,
                $this->getCacheTTL()
            );
        }

        return $accessibleInstances;
    }

    /**
     * @param string[] $resourceUris
     * @param string[] $permissionsToCheck
     */
    public function validatePermissions(array $resourceUris, array $permissionsToCheck): void
    {
        $this->getService()->validatePermissions($resourceUris, $permissionsToCheck);
    }

    private function getCache(): ?common_cache_Cache
    {
        $cache = $this->getOption(self::OPTION_CACHE);

        if (!is_array($cache)) {
            return null;
        }

        $cacheEnabled = filter_var($cache[self::OPTION_CACHE_ENABLED], FILTER_VALIDATE_BOOLEAN);

        if (!$cacheEnabled) {
            return null;
        }

        return $this->getServiceLocator()->get(common_cache_Cache::SERVICE_ID);
    }

    private function getCacheTTL(): ?int
    {
        $cache = $this->getOption(self::OPTION_CACHE);

        if (!is_array($cache)) {
            return null;
        }

        return $cache[self::OPTION_CACHE_TTL] ?? null;
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

    private function getCacheKeyFactory(): SecureResourceServiceCacheKeyFactory
    {
        return $this->getServiceLocator()->get(SecureResourceServiceCacheKeyFactory::class);
    }

    private function getService(): SecureResourceServiceInterface
    {
        if ($this->service) {
            return $this->service;
        }

        $serviceClass = $this->getOption(self::OPTION_SERVICE);

        if (empty($serviceClass)) {
            throw new RuntimeException('Service is not configured');
        }

        $this->service = new $serviceClass([]);
        $this->service->setServiceLocator($this->getServiceLocator());

        return $this->service;
    }
}
