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

use common_persistence_Persistence;
use core_kernel_classes_Property;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Connection;
use oat\generis\model\OntologyRdfs;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\cache\SimpleCache;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;
use oat\tao\model\Lists\Business\Contract\ParentPropertyListRepositoryInterface;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use Psr\SimpleCache\CacheInterface;

class ParentPropertyListCachedRepository extends ConfigurableService implements ParentPropertyListRepositoryInterface
{
    private const CACHE_MASK = 'depends-on-property-%s-%s';

    public function deleteCache(array $options): void
    {
        return; //FIXME
        if (!isset($options['propertyUri'])) {
            // remove or find list uri items
            // find all properties with list Uris
            $parentPropertiesList = $this->getPropertiesByListUris([$options['listUri']]);

            if (empty($parentPropertiesList)) {
                return;
            }

            foreach ($parentPropertiesList as $property) {
                $this->getCache()->delete(sprintf(self::CACHE_MASK, $property, $options['listUri']));
            }
        }

        $this->getCache()->delete(sprintf(self::CACHE_MASK, $options['propertyUri'], $options['listUri']));
    }

    public function findAllUris(array $options): array
    {
        /** @var core_kernel_classes_Property $property */
        $property = $options['property'];
        $listUri = $options['listUri'] ?? $property->getRange()->getUri();

        $cacheKey = sprintf(self::CACHE_MASK, $property->getUri(), $listUri);

        if ($this->getCache()->has($cacheKey)) {
            return $this->getCache()->get($cacheKey);
        }

        $uris = $this->getParentPropertyListRepository()->findAllUris($options);

        $this->getCache()->set($cacheKey, $uris);

        return $uris;
    }

    private function getCache(): CacheInterface
    {
        return $this->getServiceLocator()->get(SimpleCache::SERVICE_ID);
    }

    private function getParentPropertyListRepository(): ParentPropertyListRepositoryInterface
    {
        return $this->getServiceLocator()->get(ParentPropertyListRepository::class);
    }
}
