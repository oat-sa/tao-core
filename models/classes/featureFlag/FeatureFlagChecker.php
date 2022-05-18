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
 */

declare(strict_types=1);

namespace oat\tao\model\featureFlag;

use core_kernel_classes_Triple;
use oat\generis\model\data\Ontology;
use oat\oatbox\cache\SimpleCache;
use oat\oatbox\service\ConfigurableService;

class FeatureFlagChecker extends ConfigurableService implements FeatureFlagCheckerInterface
{
    private const ONTOLOGY_SUBJECT = 'http://www.tao.lu/Ontologies/TAO.rdf#featureFlags';
    private const ONTOLOGY_PREDICATE = 'http://www.tao.lu/Ontologies/TAO.rdf#featureFlags';

    public function isEnabled(string $feature): bool
    {
        if (array_key_exists($feature, $_ENV)) {
            return filter_var($_ENV[$feature], FILTER_VALIDATE_BOOLEAN) ?? false;
        }

        return $this->get($feature);
    }

    public function clearCache(): int
    {
        $cache = $this->getSimpleCache();
        $ontology = $this->getOntology();
        $resource = $ontology->getResource(self::ONTOLOGY_SUBJECT);

        $count = 0;

        /** @var core_kernel_classes_Triple $triple */
        foreach ($resource->getRdfTriples() as $triple) {
            $predicate = $triple->predicate;

            if ($cache->has($predicate)) {
                $cache->delete($predicate);
                $count++;
            }
        }

        return $count;
    }

    public function list(): array
    {
        $ontology = $this->getOntology();
        $resource = $ontology->getResource(self::ONTOLOGY_SUBJECT);
        $output = [];

        /** @var core_kernel_classes_Triple $triple */
        foreach ($resource->getRdfTriples() as $triple) {
            $featureFlagName = str_replace(self::ONTOLOGY_PREDICATE . '_', '', $triple->predicate);

            if ($triple->predicate === 'http://www.tao.lu/Ontologies/TAO.rdf#UpdatedAt') {
                continue;
            }

            if (array_key_exists($featureFlagName, $_ENV)) {
                $output[$featureFlagName] = filter_var($_ENV[$featureFlagName], FILTER_VALIDATE_BOOLEAN) ?? false;

                continue;
            }

            $output[$featureFlagName] = filter_var($triple->object, FILTER_VALIDATE_BOOLEAN) ?? false;
        }

        return $output;
    }

    public function save(string $featureFlagName, bool $value): void
    {
        $featureFlagName = $this->getPersistenceName($featureFlagName);

        $ontology = $this->getOntology();
        $resource = $ontology->getResource(self::ONTOLOGY_SUBJECT);
        $resource->editPropertyValues($ontology->getProperty($featureFlagName), var_export($value, true));
    }

    private function get(string $featureFlagName): bool
    {
        $featureFlagName = $this->getPersistenceName($featureFlagName);

        $cache = $this->getSimpleCache();

        if ($cache->has($featureFlagName)) {
            return filter_var($cache->get($featureFlagName), FILTER_VALIDATE_BOOLEAN) ?? false;
        }

        $ontology = $this->getOntology();
        $resource = $ontology->getResource(self::ONTOLOGY_SUBJECT);
        $value = (string)$resource->getOnePropertyValue($ontology->getProperty($featureFlagName));
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ?? false;

        $cache->set($featureFlagName, $value);

        return $value;
    }

    private function getPersistenceName(string $featureFlagName): string
    {
        return self::ONTOLOGY_PREDICATE . '_' . $featureFlagName;
    }

    private function getOntology(): Ontology
    {
        return $this->getServiceManager()->get(Ontology::SERVICE_ID);
    }

    private function getSimpleCache(): SimpleCache
    {
        return $this->getServiceManager()->get(SimpleCache::SERVICE_ID);
    }
}
