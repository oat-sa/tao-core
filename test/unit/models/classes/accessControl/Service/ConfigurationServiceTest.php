<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\accessControl\Service;

use oat\tao\model\accessControl\AccessControlServiceProvider;
use oat\tao\model\tenantConfiguration\Service\ConfigurationService as TenantConfigurationService;
use oat\tao\model\accessControl\Service\ConfigurationService;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ConfigurationServiceTest extends TestCase
{
    public function testContainerWorksWithoutRegisteredFetcher(): void
    {
        $subject = $this->createService();
        self::assertNull((new TenantConfigurationService())->fetch('testRunnerConfiguration'));
        self::assertNull($subject->fetchConfiguration('spellchecker.external_providers', 'tenant-id'));
    }

    /**
     * @dataProvider requestProvider
     */
    public function testDelegatesToRegisteredFetcher(?string $tenantId): void
    {
        $configuration = [['providerName' => 'wproofreader', 'enabled' => false]];
        $fetcher = $this->createMock(TenantConfigurationService::class);
        $fetcher->expects(self::once())->method('fetch')
            ->with('spellchecker.external_providers', $tenantId)
            ->willReturn($configuration);
        $subject = $this->createService($fetcher);

        self::assertSame(
            $configuration,
            $subject->fetchConfiguration('spellchecker.external_providers', $tenantId)
        );
    }

    public function requestProvider(): array
    {
        return [
            'existing caller defaults' => [null],
            'explicit tenant with cache' => ['tenant-id'],
        ];
    }

    public function testPropagatesFetcherFailure(): void
    {
        $exception = new RuntimeException('Fetch failed.', 424);
        $fetcher = $this->createMock(TenantConfigurationService::class);
        $fetcher->method('fetch')->willThrowException($exception);
        $this->expectExceptionObject($exception);

        (new ConfigurationService($fetcher))->fetchConfiguration('testRunnerConfiguration');
    }

    private function createService(?TenantConfigurationService $fetcher = null): ConfigurationService
    {
        $container = new ContainerBuilder();
        $instanceof = [];
        $configurator = new ContainerConfigurator(
            $container,
            new PhpFileLoader($container, new FileLocator()),
            $instanceof,
            __FILE__,
            __FILE__
        );
        (new AccessControlServiceProvider())($configurator);
        foreach (array_keys($container->getDefinitions()) as $id) {
            if (
                !in_array($id, [
                    'service_container',
                    ConfigurationService::class,
                    TenantConfigurationService::class,
                ], true)
            ) {
                $container->removeDefinition($id);
            }
        }
        if ($fetcher !== null) {
            $container->register(TenantConfigurationService::class)->setSynthetic(true)->setPublic(true);
        }
        $container->compile();
        if ($fetcher !== null) {
            $container->set(TenantConfigurationService::class, $fetcher);
        }

        return $container->get(ConfigurationService::class);
    }
}
