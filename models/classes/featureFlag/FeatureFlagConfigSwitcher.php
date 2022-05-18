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
use oat\oatbox\AbstractRegistry;
use oat\tao\model\ClientLibConfigRegistry;
use Psr\Container\ContainerInterface;

class FeatureFlagConfigSwitcher
{
    private const GLOBAL_FEATURE_FLAG_CLIENT_CONFIG_HANDLERS = 'featureFlagClientConfigHandlers';
    private const GLOBAL_FEATURE_FLAG_EXTENSION_CONFIG_HANDLERS = 'featureFlagExtensionConfigHandlers';

    /** @var AbstractRegistry */
    private $registry;

    /** @var ContainerInterface */
    private $container;

    /** @var common_ext_ExtensionsManager */
    private $extensionsManager;

    public function __construct(
        ClientLibConfigRegistry $registry,
        common_ext_ExtensionsManager $extensionsManager,
        ContainerInterface $container
    ) {
        $this->registry = $registry::getRegistry();
        $this->container = $container;
        $this->extensionsManager = $extensionsManager;
    }

    public function getSwitchedClientConfig(): array
    {
        return $this->handle($this->getClientConfigHandlers(), $this->registry->getMap());
    }

    public function getSwitchedExtensionConfig(string $extension, string $configName): array
    {
        $configs = $this->extensionsManager->getExtensionById($extension)->getConfig($configName);

        if (!$configs) {
            return [];
        }

        return $this->handle($this->getExtensionConfigHandlers($extension, $configName), $configs);
    }

    public function addClientConfigHandler(string $handler): void
    {
        $this->registry->set(
            self::GLOBAL_FEATURE_FLAG_CLIENT_CONFIG_HANDLERS,
            array_unique(
                array_merge(
                    $this->registry->getMap()[self::GLOBAL_FEATURE_FLAG_CLIENT_CONFIG_HANDLERS] ?? [],
                    [
                        $handler
                    ]
                )
            )
        );
    }

    public function removeClientConfigHandler(string $handler): void
    {
        $handlers = $this->registry->getMap()[self::GLOBAL_FEATURE_FLAG_CLIENT_CONFIG_HANDLERS] ?? [];

        foreach ($handlers as $key => $value) {
            if ($value === $handler) {
                unset($handlers[$key]);
            }
        }

        $this->registry->set(self::GLOBAL_FEATURE_FLAG_CLIENT_CONFIG_HANDLERS, $handlers);
    }

    public function addExtensionConfigHandler(string $extension, string $configName, string $handler): void
    {
        $handlers = $this->registry->getMap()[self::GLOBAL_FEATURE_FLAG_EXTENSION_CONFIG_HANDLERS] ?? [];

        if (!isset($handlers[$extension])) {
            $handlers[$extension] = [];
        }

        if (!isset($handlers[$extension][$configName])) {
            $handlers[$extension][$configName] = [];
        }

        $handlers[$extension][$configName][] = $handler;
        $handlers[$extension][$configName] = array_unique($handlers[$extension][$configName]);

        $this->registry->set(self::GLOBAL_FEATURE_FLAG_EXTENSION_CONFIG_HANDLERS, $handlers);
    }

    public function removeExtensionConfigHandler(string $extension, string $configName, string $handler): void
    {
        $handlers = $this->registry->getMap()[self::GLOBAL_FEATURE_FLAG_EXTENSION_CONFIG_HANDLERS];

        foreach ($handlers[$extension][$configName] ?? [] as $key => $value) {
            if ($value === $handler) {
                unset($handlers[$extension][$configName][$key]);
            }
        }

        $this->registry->set(self::GLOBAL_FEATURE_FLAG_EXTENSION_CONFIG_HANDLERS, $handlers);
    }

    private function handle(array $handlers, array $config): array
    {
        foreach ($handlers as $handlerId) {
            if (!$this->container->has($handlerId)) {
                continue;
            }

            /** @var FeatureFlagConfigHandlerInterface $handler */
            $handler = $this->container->get($handlerId);

            if (!$handler instanceof FeatureFlagConfigHandlerInterface) {
                continue;
            }

            $config = $handler($config);
        }

        return $config;
    }

    private function getClientConfigHandlers(): array
    {
        return $this->registry->getMap()[self::GLOBAL_FEATURE_FLAG_CLIENT_CONFIG_HANDLERS] ?? [];
    }

    private function getExtensionConfigHandlers(string $extension, string $configName): array
    {
        return $this->registry->getMap()[self::GLOBAL_FEATURE_FLAG_EXTENSION_CONFIG_HANDLERS][$extension][$configName] ?? [];
    }
}
