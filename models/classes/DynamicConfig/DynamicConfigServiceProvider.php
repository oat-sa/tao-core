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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\DynamicConfig;

use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\oatbox\log\LoggerService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class DynamicConfigServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(
                DynamicConfigProviderInterface::class,
                EnvironmentVariableConfigProvider::class
            )
            ->public()
            ->args(
                [
                    [
                        DynamicConfigProviderInterface::LOGOUT_URL_CONFIG_NAME => env(
                            'default::'
                            . EnvironmentVariableConfigProvider::ENV_REDIRECT_AFTER_LOGOUT_URL
                        ),
                        DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME => env(
                            'default::' . EnvironmentVariableConfigProvider::ENV_PORTAL_URL
                        ),
                        DynamicConfigProviderInterface::LOGIN_URL_CONFIG_NAME => env(
                            'default::' . EnvironmentVariableConfigProvider::ENV_TAO_LOGIN_URL
                        ),
                    ],
                    service(LoggerService::SERVICE_ID),
                ]
            );
    }
}
