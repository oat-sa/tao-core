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

namespace oat\tao\model\routing\ServiceProvider;

use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\oatbox\log\LoggerService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\routing\AnnotationReaderService;
use oat\tao\model\routing\Listener\AnnotationCacheWarmupListener;
use oat\tao\model\routing\ResolverFactory;
use oat\tao\model\routing\Service\ActionFinder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @codeCoverageIgnore
 */
class RoutingServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(ActionFinder::class, ActionFinder::class)
            ->args(
                [
                    service(ContainerServiceProviderInterface::CONTAINER_SERVICE_ID),
                    service(LoggerService::SERVICE_ID),
                ]
            )
            ->public();

        $services
            ->set(ResolverFactory::class, ResolverFactory::class)
            ->args(
                [
                    service(ServiceManager::class),
                ]
            );

        $services
            ->set(AnnotationCacheWarmupListener::class, AnnotationCacheWarmupListener::class)
            ->public()
            ->args(
                [
                    service(AnnotationReaderService::class),
                    service(\common_ext_ExtensionsManager::SERVICE_ID)
                ]
            );
    }
}
