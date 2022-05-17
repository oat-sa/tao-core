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
 */

declare(strict_types=1);

namespace oat\tao\model\featureVisibility;

use InvalidArgumentException;
use oat\oatbox\AbstractRegistry;
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\model\featureFlag\FeatureFlagClientConfigHandlerInterface;
use Psr\Container\ContainerInterface;

class FeatureVisibilityService
{
    public const HIDE_PARAM = 'hide';
    public const SHOW_PARAM = 'show';

    private const GLOBAL_UI_CONFIG_NAME = 'services/features';
    private const GLOBAL_FEATURE_FLAG_CONFIG_HANDLERS = 'featureFlagConfigHandlers';

    /** @var AbstractRegistry */
    private $registry;

    /** @var ContainerInterface */
    private $container;

    public function __construct(ClientLibConfigRegistry $registry, ContainerInterface $container)
    {
        $this->registry = $registry::getRegistry();
        $this->container = $container;
    }

    public function getClientConfig(): array
    {
        $handlers = $this->registry->getMap()[self::GLOBAL_FEATURE_FLAG_CONFIG_HANDLERS] ?? [];

        $libConfigs = $this->registry->getMap();

        foreach ($handlers as $handlerId) {
            if (!$this->container->has($handlerId)) {
                continue;
            }

            /** @var FeatureFlagClientConfigHandlerInterface $handler */
            $handler = $this->container->get($handlerId);

            if (!$handler instanceof FeatureFlagClientConfigHandlerInterface) {
                continue;
            }

            $libConfigs = $handler($libConfigs);
        }

        return $libConfigs;
    }

    public function addHandler(string $handler): void
    {
        $handlers = $this->registry->getMap()[self::GLOBAL_FEATURE_FLAG_CONFIG_HANDLERS] ?? [];

        $this->registry->set(
            self::GLOBAL_FEATURE_FLAG_CONFIG_HANDLERS,
            array_unique(array_merge($handlers, [$handler]))
        );
    }

    public function removeHandler(string $handler)
    {
        $handlers = $this->registry->getMap()[self::GLOBAL_FEATURE_FLAG_CONFIG_HANDLERS] ?? [];

        foreach ($handlers as $key => $value) {
            if ($value === $handler) {
                unset($handlers[$key]);
            }
        }

        $this->registry->set(self::GLOBAL_FEATURE_FLAG_CONFIG_HANDLERS, $handlers);
    }

    public function showFeature(string $featureName): void
    {
        $this->updateFeatureVisibility('updateConfig', [$featureName => self::SHOW_PARAM]);
    }

    public function hideFeature(string $featureName): void
    {
        $this->updateFeatureVisibility('updateConfig', [$featureName => self::HIDE_PARAM]);
    }

    public function setFeaturesVisibility(array $featureNameVisibilityMap): void
    {
        $this->updateFeatureVisibility('updateConfig', $featureNameVisibilityMap);
    }

    public function removeFeature(string $featureName): void
    {
        $this->updateFeatureVisibility('removeFeatureFromConfig', $featureName);
    }

    private function updateFeatureVisibility(string $configUpdaterCallableName, $configUpdaterArg): void
    {
        $existingConfig = $this->registry->get(self::GLOBAL_UI_CONFIG_NAME);
        if ($existingConfig === '') {
            $existingConfig = [];
        }

        $updatedConfig = $this->{$configUpdaterCallableName}($existingConfig, $configUpdaterArg);

        $this->registry->set(self::GLOBAL_UI_CONFIG_NAME, $updatedConfig);
    }

    private function updateConfig(array $existingConfig, array $newStatuses): array
    {
        foreach ($newStatuses as $featureName => $featureValue) {
            if (!in_array($featureValue, [self::HIDE_PARAM, self::SHOW_PARAM], true)) {
                throw new InvalidArgumentException(sprintf(
                    'Feature value should be either %s or %s, %s given',
                    self::SHOW_PARAM,
                    self::HIDE_PARAM,
                    $featureValue
                ));
            }
            $existingConfig['visibility'][$featureName] = $featureValue;
        }

        return $existingConfig;
    }

    private function removeFeatureFromConfig(array $existingConfig, string $featureName): array
    {
        unset($existingConfig['visibility'][$featureName]);

        return $existingConfig;
    }
}
