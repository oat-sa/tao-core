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
 * Copyright (c) 2022-2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\featureVisibility;

use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\model\ClientLibRegistry;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class FeatureVisibilityServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(ClientLibConfigRegistry::class)
            ->public()
            ->factory(ClientLibConfigRegistry::class . '::getRegistry');

        $services
            ->set(ClientLibRegistry::class)
            ->public()
            ->factory(ClientLibRegistry::class . '::getRegistry');

        $services
            ->set(FeatureVisibilityService::class, FeatureVisibilityService::class)
            ->public()
            ->args([
                service(ClientLibConfigRegistry::class),
            ]);
    }
}
