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
use Psr\SimpleCache\CacheInterface;
use oat\tao\models\TaskOrchestrator\TaskOrchestratorClient;
use oat\tao\models\TaskOrchestrator\TaskOrchestratorEmailService;

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

        $services->set('GuzzleClientForTaskOrchestrator', GuzzleHttpClient::class);

        // Opcjonalnie: Rejestracja serwisu cache dla tokenów
        // Używamy anonimowej klasy jako fallback, jeśli Tao nie ma wbudowanego PSR-6/PSR-16 Cache
        $services->set('TaskOrchestratorTokenCache', CacheInterface::class)
            ->factory([self::class, 'createTaskOrchestratorTokenCache']);

        // Rejestracja TaskOrchestratorClient
        $services->set(TaskOrchestratorClient::class)
            ->arg('$baseUrl', getenv('TASK_ORCHESTRATOR_API_URL'))
            ->arg('$authServerUri', getenv('TASK_ORCHESTRATOR_AUTH_SERVER_URI'))
            ->arg('$clientId', getenv('TASK_ORCHESTRATOR_CLIENT_ID'))
            ->arg('$clientSecret', getenv('TASK_ORCHESTRATOR_CLIENT_SECRET'))
            ->arg('$httpClient', service('GuzzleClientForTaskOrchestrator'))
            ->arg('$cache', service('TaskOrchestratorTokenCache'));

        // Rejestracja TaskOrchestratorEmailService
        $services->set(TaskOrchestratorEmailService::class)
            ->arg('$client', service(TaskOrchestratorClient::class))
            ->arg('$tenantId', getenv('TAO_TENANT_ID'))
            ->arg('$actorLogin', getenv('TAO_TASK_ORCHESTRATOR_ACTOR_LOGIN'));
    }

    public static function createTaskOrchestratorTokenCache(): CacheInterface
    {
        // To jest bardzo prosta i tymczasowa implementacja cache.
        // W prawdziwym środowisku należy użyć istniejącego PSR-6/PSR-16 cache z Tao.
        return new class implements CacheInterface {
            private array $data = [];

            public function get(string $key, mixed $default = null): mixed
            {
                return $this->data[$key] ?? $default;
            }

            public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
            {
                $this->data[$key] = $value;

                return true;
            }

            public function delete(string $key): bool
            {
                unset($this->data[$key]);

                return true;
            }

            public function clear(): bool
            {
                $this->data = [];

                return true;
            }

            public function getMultiple(iterable $keys, mixed $default = null): iterable
            {
                throw new \RuntimeException('Not implemented');
            }

            public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
            {
                throw new \RuntimeException('Not implemented');
            }

            public function deleteMultiple(iterable $keys): bool
            {
                throw new \RuntimeException('Not implemented');
            }

            public function has(string $key): bool
            {
                return isset($this->data[$key]);
            }
        };
    }
}
