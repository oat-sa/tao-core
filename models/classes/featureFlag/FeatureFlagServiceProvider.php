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

namespace oat\tao\model\featureFlag;

use common_ext_ExtensionsManager;
use oat\generis\model\data\Ontology;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\oatbox\cache\SimpleCache;
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\model\featureFlag\Listener\FeatureFlagCacheWarmupListener;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepository;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepositoryInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @codeCoverageIgnore
 */
class FeatureFlagServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(FeatureFlagListService::class, FeatureFlagListService::class)
            ->public()
            ->args(
                [
                    service(FeatureFlagRepositoryInterface::class),
                ]
            );

        $services
            ->set(FeatureFlagConfigSwitcher::class)
            ->public()
            ->args(
                [
                    service(ClientLibConfigRegistry::class),
                    service(common_ext_ExtensionsManager::class),
                    service(self::CONTAINER_SERVICE_ID),
                ]
            );

        $services
            ->set(FeatureFlagRepositoryInterface::class, FeatureFlagRepository::class)
            ->public()
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(SimpleCache::SERVICE_ID),
                ]
            );

        $services
            ->set(FeatureFlagCacheWarmupListener::class, FeatureFlagCacheWarmupListener::class)
            ->public()
            ->args(
                [
                    service(FeatureFlagRepositoryInterface::class)
                ]
            );
    }
}
