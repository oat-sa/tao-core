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

namespace oat\tao\model\Lists\DataAccess\Repository;

use core_kernel_classes_Property;
use InvalidArgumentException;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\cache\SimpleCache;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;
use oat\tao\model\Lists\Business\Contract\ParentPropertyListRepositoryInterface;
use Psr\SimpleCache\CacheInterface;

class ParentPropertyListCachedRepository extends ConfigurableService implements ParentPropertyListRepositoryInterface
{
    private const CACHE_MASK = 'depends_on_property-%s-%s';
    private const LIST_CACHE_MASK = 'depends_on_property-%s';


    public function deleteCache(array $options): void
    {
        if (empty($options['listUri'])) {
            throw new InvalidArgumentException('listUri is required to clear the cache');
        }

        if (empty($options['propertyUri'])) {
            $this->removeUsingListUri($options['listUri']);
        }
        $childListUris = $this->getDependencyRepository()->findChildListUris([
                            'parentListUri' => $options['listUri']
                        ]);
        foreach ($childListUris as $uri) {
            $this->removeUsingListUri($uri);
        }
    }

    public function findAllUris(array $options): array
    {
        /** @var core_kernel_classes_Property $property */
        $property = $options['property'] ?? null;
        if (!$property) {
            return $this->getParentPropertyListRepository()->findAllUris($options);
        }
        $listUri = $options['listUri'] ?? $property->getRange()->getUri();
        $cacheKey = sprintf(self::CACHE_MASK, $property->getUri(), $listUri);
        $listCacheKey = sprintf(self::LIST_CACHE_MASK, $listUri);
        $currentValues = [];
        $listCacheValues = [$cacheKey];

        if ($this->getCache()->has($listCacheKey)) {
            $currentValues = $this->getCache()->get($listCacheKey);
            $listCacheValues = array_unique(array_merge($currentValues, $listCacheValues));
        }

        if ($currentValues !== $listCacheValues) {
            $this->getCache()->set($listCacheKey, $listCacheValues);
        }

        if ($this->getCache()->has($cacheKey)) {
            return $this->getCache()->get($cacheKey);
        }

        $uris = $this->getParentPropertyListRepository()->findAllUris($options);

        $this->getCache()->set($cacheKey, $uris);

        return $uris;
    }

    private function removeUsingListUri(string $uri): void
    {
        $listCacheKey = sprintf(self::LIST_CACHE_MASK, $uri);
        if ($this->getCache()->has($listCacheKey)) {
            $listCacheValues = $this->getCache()->get($listCacheKey);
            foreach ($listCacheValues as $value) {
                [$key, $propertyUri, $listUri]  = explode('-', $value);
                $this->getCache()->delete(sprintf(self::CACHE_MASK, $propertyUri, $listUri));
            }
            $this->getCache()->delete($listCacheKey);
        }
    }

    private function getCache(): CacheInterface
    {
        return $this->getServiceLocator()->get(SimpleCache::SERVICE_ID);
    }

    private function getParentPropertyListRepository(): ParentPropertyListRepositoryInterface
    {
        return $this->getServiceLocator()->get(ParentPropertyListRepository::class);
    }

    private function getDependencyRepository(): DependencyRepositoryInterface
    {
        return $this->getServiceLocator()->get(DependencyRepository::class);
    }
}
