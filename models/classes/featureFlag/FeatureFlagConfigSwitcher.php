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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\featureFlag;

use common_ext_ExtensionsManager;
use Psr\Container\ContainerInterface;
use oat\tao\model\clientConfig\ClientLibConfigSwitcher;

class FeatureFlagConfigSwitcher
{
    private ContainerInterface $container;
    private common_ext_ExtensionsManager $extensionsManager;
    private ClientLibConfigSwitcher $clientLibConfigSwitcher;

    /** @var string[][] */
    private array $extensionConfigHandlers = [];

    public function __construct(
        common_ext_ExtensionsManager $extensionsManager,
        ContainerInterface $container,
        ClientLibConfigSwitcher $clientLibConfigSwitcher
    ) {
        $this->container = $container;
        $this->extensionsManager = $extensionsManager;
        $this->clientLibConfigSwitcher = $clientLibConfigSwitcher;
    }

    /** @deprecated Use oat\tao\model\clientConfig\ClientLibConfigSwitcher::getSwitchedClientLibConfig() instead */
    public function getSwitchedClientConfig(): array
    {
        return $this->clientLibConfigSwitcher->getSwitchedClientLibConfig();
    }

    public function getSwitchedExtensionConfig(string $extension, string $configName): array
    {
        $configs = $this->extensionsManager->getExtensionById($extension)->getConfig($configName);

        if (!$configs) {
            return [];
        }

        return $this->handle($this->getExtensionConfigHandlers($extension, $configName), $configs);
    }

    /**
     * @deprecated Implement oat\tao\model\clientConfig\ClientLibConfigHandlerInterface for necessary handlers and use
     *             'tao.client_lib_config.handler' tag to add config handler via DI.
     */
    public function addClientConfigHandler(string $handler): void
    {
    }

    public function addExtensionConfigHandler(string $extension, string $configName, string $handler): void
    {
        $key = $extension . '_' . $configName;

        $this->extensionConfigHandlers[$key] = $this->extensionConfigHandlers[$key] ?? [];
        $this->extensionConfigHandlers[$key][$handler] = $handler;
    }

    private function handle(array $handlers, array $config): array
    {
        foreach ($handlers as $handlerId) {
            if (!$this->container->has($handlerId)) {
                continue;
            }

            /** @var FeatureFlagConfigHandlerInterface $handler */
            $handler = $this->container->get($handlerId);

            if ($handler instanceof FeatureFlagConfigHandlerInterface) {
                $config = $handler($config);
            }
        }

        return $config;
    }

    private function getExtensionConfigHandlers(string $extension, string $configName): array
    {
        return $this->extensionConfigHandlers[$extension . '_' . $configName] ?? [];
    }
}
