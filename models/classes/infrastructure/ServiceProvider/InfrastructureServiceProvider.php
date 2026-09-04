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
 * Copyright (c) 2021-2023 (update and modification) Open Assessment Technologies SA;
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

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class InfrastructureServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(SharedCache::class, SharedCache::class)
            ->public()
            ->call('setServiceManager', [service(ServiceManager::class)]);

        $services
            ->set('GuzzleClientForTaskOrchestrator', GuzzleHttpClient::class)
            ->public();

        $services
            ->set(TaskOrchestratorClient::class)
            ->public()
            ->arg('$baseUrl', (string) (getenv('TASK_ORCHESTRATOR_API_URL') ?: ''))
            ->arg('$authServerUri', (string) (getenv('TASK_ORCHESTRATOR_AUTH_SERVER_URI') ?: ''))
            ->arg('$clientId', (string) (getenv('TASK_ORCHESTRATOR_CLIENT_ID') ?: ''))
            ->arg('$clientSecret', (string) (getenv('TASK_ORCHESTRATOR_CLIENT_SECRET') ?: ''))
            ->arg('$httpClient', service('GuzzleClientForTaskOrchestrator'))
            ->arg('$cache', null);

        $services
            ->set(TaskOrchestratorEmailService::class)
            ->public()
            ->arg('$client', service(TaskOrchestratorClient::class))
            ->arg('$tenantId', (string) (getenv('TAO_TENANT_ID') ?: ''))
            ->arg('$actorLogin', (string) (getenv('TAO_TASK_ORCHESTRATOR_ACTOR_LOGIN') ?: ''));

        $services
            ->set(CommentMentionDeepLinkBuilder::class)
            ->public();
    }
}
