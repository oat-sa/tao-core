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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\webhooks;

use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\auth\BasicAuthType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class WebhookServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(BasicAuthType::class)
            ->public();

        $services->set(WebhookAuthService::class)
            ->public()
            ->args([
                service(BasicAuthType::class)
            ]);

        $services->set(WebhookRdfRegistry::class)
            ->public()
            ->autowire(true);

        $services->set(WebhookRegistryFactory::class)
            ->public()
            ->autowire(true);

        $registryType = env('default::WEBHOOK_REGISTRY_TYPE');

        $services->set(WebhookRegistryInterface::class)
            ->public()
            ->factory([service(WebhookRegistryFactory::class), 'create'])
            ->args([$registryType]);
    }
}
