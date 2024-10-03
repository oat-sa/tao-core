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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\clientConfig;

use common_ext_ExtensionsManager;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\oatbox\log\LoggerService;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\helpers\dateFormatter\DateFormatterFactory;
use oat\tao\model\asset\AssetService;
use oat\tao\model\ClientLibRegistry;
use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepositoryInterface;
use oat\tao\model\menu\MenuService;
use oat\tao\model\routing\ResolverFactory;
use oat\tao\model\security\xsrf\TokenService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use tao_helpers_Mode;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ClientConfigServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(ClientConfigStorage::class, ClientConfigStorage::class)
            ->public()
            ->args(
                [
                    service(TokenService::SERVICE_ID),
                    service(ClientLibRegistry::class),
                    service(FeatureFlagConfigSwitcher::class),
                    service(AssetService::SERVICE_ID),
                    service(common_ext_ExtensionsManager::SERVICE_ID),
                    service(ClientConfigService::SERVICE_ID),
                    service(UserLanguageServiceInterface::SERVICE_ID),
                    service(FeatureFlagRepositoryInterface::class),
                    service(ResolverFactory::class),
                    service(LoggerService::SERVICE_ID),
                    service(SessionService::SERVICE_ID),
                    service(tao_helpers_Mode::class),
                    service(DateFormatterFactory::class),
                    service(MenuService::class),
                ]
            );
    }
}
