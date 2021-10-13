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

use InvalidArgumentException;
use core_kernel_classes_Property;
use oat\oatbox\cache\SimpleCache;
use Psr\SimpleCache\CacheInterface;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;
use oat\tao\model\Lists\Business\Contract\ParentPropertyListRepositoryInterface;

class ParentPropertyListCachedRepository extends ConfigurableService implements ParentPropertyListRepositoryInterface
{
    private const CACHE_MASK = 'depends_on_property-%s-%s';
    private const LIST_CACHE_MASK = 'depends_on_property-%s';

    public function deleteCache(array $options): void
    {
        if (empty($options['listUri'])) {
            throw new InvalidArgumentException('listUri is required to clear the cache');
        }

        $this->removeUsingListUri($options['listUri']);

        $childListUris = $this->getDependencyRepository()->findChildListUris(
            [
                'parentListUri' => $options['listUri']
            ]
        );

        foreach ($childListUris as $uri) {
            $this->removeUsingListUri($uri);
        }
    }

    public function findAllUris(array $options): array
    {
        /** @var core_kernel_classes_Property $property */
        $property = $options['property'] ?? null;

        $cache = $this->getCache();

        # @TODO Remove requirement from property parameter
        if (!$property) {
            return $this->getParentPropertyListRepository()->findAllUris($options);
        }

        $listUri = $options['listUri'] ?? $property->getRange()->getUri();
        $cacheKey = sprintf(self::CACHE_MASK, $property->getUri(), $listUri);
        $listCacheKey = sprintf(self::LIST_CACHE_MASK, $listUri);

        $currentValues = [];
        $listCacheValues = [$cacheKey];

        if ($cache->has($listCacheKey)) {
            $currentValues = $cache->get($listCacheKey);
            $listCacheValues = array_unique(array_merge($currentValues, $listCacheValues));
        }

        if ($currentValues !== $listCacheValues) {
            $cache->set($listCacheKey, $listCacheValues);
        }

        if ($cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $uris = $this->getParentPropertyListRepository()->findAllUris($options);

        $cache->set($cacheKey, $uris);

        return $uris;
    }

    private function removeUsingListUri(string $uri): void
    {
        $listCacheKey = sprintf(self::LIST_CACHE_MASK, $uri);
        $cache = $this->getCache();

        if ($cache->has($listCacheKey)) {
            $listCacheValues = $cache->get($listCacheKey);

            foreach ($listCacheValues as $value) {
                [$key, $propertyUri, $listUri]  = explode('-', $value);
                $cache->delete(sprintf(self::CACHE_MASK, $propertyUri, $listUri));
            }

            $cache->delete($listCacheKey);
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
        return $this->getServiceLocator()->getContainer()->get(DependencyRepository::class);
    }
}
