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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\featureFlag\Repository;

use core_kernel_classes_Triple;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\TaoOntology;
use Psr\SimpleCache\CacheInterface;

class FeatureFlagRepository implements FeatureFlagRepositoryInterface
{
    private const ONTOLOGY_SUBJECT = 'http://www.tao.lu/Ontologies/TAO.rdf#featureFlags';
    private const ONTOLOGY_PREDICATE = 'http://www.tao.lu/Ontologies/TAO.rdf#featureFlags';
    private const CACHE_LIST_KEY = 'FEATURE_FLAG_LIST';
    private const FEATURE_FLAG_PREFIX = 'FEATURE_FLAG_';

    /** @var Ontology */
    private $ontology;

    /** @var CacheInterface */
    private $cache;

    /** @var array */
    private $storageOverride;

    public function __construct(Ontology $ontology, CacheInterface $cache, array $storageOverride = null)
    {
        $this->ontology = $ontology;
        $this->cache = $cache;
        $this->storageOverride = $storageOverride ?? $_ENV;
    }

    public function get(string $featureFlagName): bool
    {
        if ($this->hasOverrideFeatureFlag($featureFlagName)) {
            return $this->getOverrideFeatureFlag($featureFlagName);
        }

        $featureFlagName = $this->getPersistenceName($featureFlagName);

        $value = $this->cache->get($featureFlagName);
        if (null !== $value) {
            return $this->filterVar($value);
        }

        $resource = $this->ontology->getResource(self::ONTOLOGY_SUBJECT);
        $value = (string)$resource->getOnePropertyValue($this->ontology->getProperty($featureFlagName));
        $value = $this->filterVar($value);

        $this->cache->set($featureFlagName, $value);

        return $value;
    }

    public function list(): array
    {
        $output = $this->getList();

        foreach ($this->storageOverride as $key => $value) {
            if (strpos($key, self::FEATURE_FLAG_PREFIX) === 0) {
                $output[$key] = $this->filterVar($this->storageOverride[$key]);
            }
        }

        /**
         * @deprecated Only here for legacy support purposes, we should rely on storage
         */
        if (!array_key_exists(FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED, $output)) {
            $output[FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED] = false;
        }

        return $output;
    }

    public function save(string $featureFlagName, bool $value): void
    {
        if (strpos($featureFlagName, self::FEATURE_FLAG_PREFIX) !== 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'FeatureFlag name needs to start with "%s"',
                    self::FEATURE_FLAG_PREFIX
                )
            );
        }

        $featureFlagName = $this->getPersistenceName($featureFlagName);

        $resource = $this->ontology->getResource(self::ONTOLOGY_SUBJECT);
        $resource->editPropertyValues($this->ontology->getProperty($featureFlagName), var_export($value, true));

        if ($this->cache->has($featureFlagName)) {
            $this->cache->delete($featureFlagName);
        }

        if ($this->cache->has(self::CACHE_LIST_KEY)) {
            $this->cache->delete(self::CACHE_LIST_KEY);
        }
    }

    public function clearCache(): int
    {
        $resource = $this->ontology->getResource(self::ONTOLOGY_SUBJECT);

        $count = 0;

        /** @var core_kernel_classes_Triple $triple */
        foreach ($resource->getRdfTriples() as $triple) {
            if ($triple->predicate === TaoOntology::PROPERTY_UPDATED_AT) {
                continue;
            }

            if ($this->cache->has($triple->predicate)) {
                $this->cache->delete($triple->predicate);
                $count++;
            }
        }

        if ($this->cache->has(self::CACHE_LIST_KEY)) {
            $this->cache->delete(self::CACHE_LIST_KEY);
        }

        return $count;
    }

    private function getList(): array
    {
        $output = $this->cache->get(self::CACHE_LIST_KEY);
        if ($output !== null) {
            return $output;
        }

        $output = $this->getListFromDb();
        $this->cache->set(self::CACHE_LIST_KEY, $output);

        return $output;
    }

    private function getListFromDb(): array
    {
        $output = [];

        $resource = $this->ontology->getResource(self::ONTOLOGY_SUBJECT);

        /** @var core_kernel_classes_Triple $triple */
        foreach ($resource->getRdfTriples() as $triple) {
            $featureFlagName = str_replace(self::ONTOLOGY_PREDICATE . '_', '', $triple->predicate);

            if ($triple->predicate === TaoOntology::PROPERTY_UPDATED_AT) {
                continue;
            }

            $output[$featureFlagName] = $this->get($featureFlagName);
        }

        return $output;
    }

    private function getPersistenceName(string $featureFlagName): string
    {
        return self::ONTOLOGY_PREDICATE . '_' . $featureFlagName;
    }

    private function filterVar($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ?? false;
    }

    private function getOverrideFeatureFlag(string $key): bool
    {
        return $this->filterVar($this->storageOverride[$key] ?? false);
    }

    private function hasOverrideFeatureFlag(string $key): bool
    {
        return array_key_exists($key, $this->storageOverride);
    }
}
