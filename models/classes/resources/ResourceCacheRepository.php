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

declare(strict_types=1);

namespace oat\tao\model\resources;

use common_persistence_KeyValuePersistence;
use oat\generis\persistence\PersistenceManager;

class ResourceCacheRepository implements ResourceRepositoryInterface
{
    /** @var ResourceRepository */
    private $repository;
    /** @var PersistenceManager */
    private $persistenceManager;
    /** @var string */
    private $persistenceName;

    public function __construct(
        ResourceRepository $repository,
        PersistenceManager $persistenceManager,
        string $persistenceName
    ) {
        $this->repository = $repository;
        $this->persistenceManager = $persistenceManager;
        $this->persistenceName = $persistenceName;
    }

    public function find(string $uri): ResourceInterface
    {
        $cache = $this->getCache();

        $key = $this->getResourceKey($uri);

        $value = $cache->get($key);

        if (false !== $value) {
            return $this->unserializeResource($value);
        }

        $resource = $this->repository->find($uri);

        $cache->set(
            $key,
            $this->serializeResource($resource)
        );

        return $resource;
    }

    public function findChildren(RdfClassInterface $class, bool $recursive): array
    {
        // TODO: move to factory
        $key = sprintf('children:%s:%s', $class->getUri(), var_export($recursive, true));

        $cache = $this->getCache();

        $childrenUris = $cache->get($key);

        if (false !== $childrenUris) {
            $cachedChildren = [];

            foreach (json_decode($childrenUris, true) as $uri) {
                $cachedChildren[] = $this->find($uri);
            }

            return $cachedChildren;
        }

        $children = $this->repository->findChildren($class, $recursive);

        $childrenUris = array_map(
            static function (RdfClassInterface $child) {
                return $child->getUri();
            },
            $children
        );

        $cache->set($key, json_encode($childrenUris));

        return $children;
    }

    private function unserializeResource(string $serializedResource): ResourceInterface
    {
        //TODO: move to serializer

        $json = json_decode($serializedResource, true);

        return new RdfClass(
            $json['payload']['uri'], $json['payload']['label']
        );
    }

    private function serializeResource(ResourceInterface $resource): string
    {
        //TODO: move to serializer

        return json_encode(
            [
                'type'    => get_class($resource),
                'payload' => [
                    'uri'   => $resource->getUri(),
                    'label' => $resource->getLabel()
                ]
            ]
        );
    }

    private function getResourceKey(string $uri): string
    {
        //TODO: move to factory

        return 'resource:' . $uri;
    }

    private function getCache(): common_persistence_KeyValuePersistence
    {
        return $this->persistenceManager->getPersistenceById($this->persistenceName);
    }
}
