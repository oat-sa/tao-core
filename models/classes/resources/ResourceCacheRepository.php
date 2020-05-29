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
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\service\InjectionAwareService;
use RuntimeException;

// TODO: warm up script is needed
class ResourceCacheRepository extends InjectionAwareService implements ResourceRepositoryInterface
{
    /** @var ResourceRepository */
    private $repository;
    /** @var PersistenceManager */
    private $persistenceManager;
    /** @var string */
    private $persistenceName;
    /** @var common_persistence_KeyValuePersistence */
    private $cache;

    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     *
     * @param ResourceRepository $repository
     * @param PersistenceManager $persistenceManager
     * @param string             $persistenceName
     */
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

    public function findInstances(RdfClassInterface $class, bool $recursive = false): array
    {
        $key = sprintf('instances:%s:%s', $class->getUri(), var_export($recursive, true));

        $cache = $this->getCache();

        $instancesUris = $cache->get($key);

        if (false !== $instancesUris) {
            $instances = [];

            foreach (json_decode($instancesUris, true) as $uri) {
                $instances[] = $this->find($uri);
            }

            return $instances;
        }

        $instances = $this->repository->findInstances($class, $recursive);

        $instancesUrl = array_map(
            static function (ResourceInterface $child) {
                return $child->getUri();
            },
            $instances
        );

        $cache->set($key, json_encode($instancesUrl));

        return $instances;
    }

    private function unserializeResource(string $serializedResource): ResourceInterface
    {
        //TODO: move to serializer

        $json = json_decode($serializedResource, true);

        switch ($json['type']) {
            case core_kernel_classes_Class::class:
                return new RdfClass(
                    $json['payload']['uri'], $json['payload']['label']
                );
                break;
            case core_kernel_classes_Resource::class:
                return new RdfResource(
                    $json['payload']['uri'], $json['payload']['label']
                );
                break;
        }

        throw new RuntimeException(sprintf('Type %s is not supported', $json['type']));
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
        if ($this->cache) {
            return $this->cache;
        }

        $this->cache = $this->persistenceManager->getPersistenceById($this->persistenceName);

        return $this->cache;
    }
}
