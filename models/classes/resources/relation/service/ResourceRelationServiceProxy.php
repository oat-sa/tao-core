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

namespace oat\tao\model\resources\relation\service;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\resources\relation\FindAllQuery;
use oat\tao\model\resources\relation\ResourceRelationCollection;

class ResourceRelationServiceProxy extends ConfigurableService implements ResourceRelationServiceInterface
{
    public const SERVICE_ID = 'tao/ResourceRelationServiceProxy';
    public const OPTION_SERVICES = 'services';

    public function addService(string $type, string $serviceId): void
    {
        $services = $this->getServices($type);

        if (!in_array($serviceId, $services[$type], true)) {
            $services[$type][] = $serviceId;
        }

        $this->setOption(self::OPTION_SERVICES, $services);
    }

    public function removeService(string $type, string $serviceId): void
    {
        $services = $this->getServices($type);

        $key = array_search($serviceId, $services[$type]);

        if ($key !== false) {
            unset($services[$type][$key]);
        }

        $this->setOption(self::OPTION_SERVICES, $services);
    }

    public function findRelations(FindAllQuery $query): ResourceRelationCollection
    {
        $relations = [];

        foreach ($this->getOption(self::OPTION_SERVICES, []) as $type => $services) {
            if ($query->getType() !== $type) {
                continue;
            }

            foreach ($services as $serviceId) {
                $relations = array_merge(
                    $relations,
                    $this->getResourceRelationService($serviceId)
                        ->findRelations($query)
                        ->getIterator()
                        ->getArrayCopy()
                );
            }
        }

        return new ResourceRelationCollection(...$relations);
    }

    private function getResourceRelationService(string $serviceId): ResourceRelationServiceInterface
    {
        return $this->getServiceLocator()->get($serviceId);
    }

    private function getServices(string $type): array
    {
        $services = (array)$this->getOption(self::OPTION_SERVICES, []);

        if (!isset($services[$type])) {
            $services[$type] = [];
        }

        return $services;
    }
}
