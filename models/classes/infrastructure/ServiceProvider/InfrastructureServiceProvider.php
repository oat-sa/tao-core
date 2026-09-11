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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2021-2026 (update and modification) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\infrastructure\ServiceProvider;

use oat\oatbox\service\ServiceManager;
use oat\tao\model\infrastructure\DataAccess\SharedCache;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use GuzzleHttp\Client as GuzzleHttpClient;
use oat\tao\model\TaskOrchestrator\TaskOrchestratorClient;
use oat\tao\model\TaskOrchestrator\TaskOrchestratorEmailService;
use oat\tao\model\TaskOrchestrator\CommentMentionDeepLinkBuilder;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class InfrastructureServiceProvider implements ContainerServiceProviderInterface
{
    /**
     * Empty-string defaults for Symfony env()->default() (parameter name, not literal).
     * NGS sets real values via docker/apps/tao/config.libsonnet.
     */
    private const PARAM_TASK_ORCHESTRATOR_API_URL_DEFAULT = 'TASK_ORCHESTRATOR_API_URL_DEFAULT';
    private const PARAM_TASK_ORCHESTRATOR_AUTH_SERVER_URI_DEFAULT = 'TASK_ORCHESTRATOR_AUTH_SERVER_URI_DEFAULT';
    private const PARAM_TASK_ORCHESTRATOR_CLIENT_ID_DEFAULT = 'TASK_ORCHESTRATOR_CLIENT_ID_DEFAULT';
    private const PARAM_TASK_ORCHESTRATOR_CLIENT_SECRET_DEFAULT = 'TASK_ORCHESTRATOR_CLIENT_SECRET_DEFAULT';
    private const PARAM_TENANT_ID_DEFAULT = 'TENANT_ID_DEFAULT';

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $parameters = $configurator->parameters();

        $parameters->set(self::PARAM_TASK_ORCHESTRATOR_API_URL_DEFAULT, '');
        $parameters->set(self::PARAM_TASK_ORCHESTRATOR_AUTH_SERVER_URI_DEFAULT, '');
        $parameters->set(self::PARAM_TASK_ORCHESTRATOR_CLIENT_ID_DEFAULT, '');
        $parameters->set(self::PARAM_TASK_ORCHESTRATOR_CLIENT_SECRET_DEFAULT, '');
        $parameters->set(self::PARAM_TENANT_ID_DEFAULT, '');

        $services
            ->set(SharedCache::class, SharedCache::class)
            ->public()
            ->call('setServiceManager', [service(ServiceManager::class)]);

        $services
            ->set('GuzzleClientForTaskOrchestrator', GuzzleHttpClient::class)
            ->public()
            ->args([[
                'timeout' => 30.0,
                'connect_timeout' => 5.0,
            ]]);

        $services
            ->set(TaskOrchestratorClient::class)
            ->public()
            ->arg(
                '$baseUrl',
                env(TaskOrchestratorClient::ENV_API_URL)
                    ->default(self::PARAM_TASK_ORCHESTRATOR_API_URL_DEFAULT)
                    ->string()
            )
            ->arg(
                '$authServerUri',
                env(TaskOrchestratorClient::ENV_AUTH_SERVER_URI)
                    ->default(self::PARAM_TASK_ORCHESTRATOR_AUTH_SERVER_URI_DEFAULT)
                    ->string()
            )
            ->arg(
                '$clientId',
                env(TaskOrchestratorClient::ENV_CLIENT_ID)
                    ->default(self::PARAM_TASK_ORCHESTRATOR_CLIENT_ID_DEFAULT)
                    ->string()
            )
            ->arg(
                '$clientSecret',
                env(TaskOrchestratorClient::ENV_CLIENT_SECRET)
                    ->default(self::PARAM_TASK_ORCHESTRATOR_CLIENT_SECRET_DEFAULT)
                    ->string()
            )
            ->arg('$httpClient', service('GuzzleClientForTaskOrchestrator'))
            ->arg('$cache', service(SharedCache::class));

        $services
            ->set(TaskOrchestratorEmailService::class)
            ->public()
            ->arg('$client', service(TaskOrchestratorClient::class))
            ->arg(
                '$tenantId',
                env(TaskOrchestratorEmailService::ENV_TENANT_ID)
                    ->default(self::PARAM_TENANT_ID_DEFAULT)
                    ->string()
            );

        $services
            ->set(CommentMentionDeepLinkBuilder::class)
            ->public();
    }
}
