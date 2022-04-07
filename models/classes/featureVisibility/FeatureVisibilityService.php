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

use common_ext_ExtensionsManager;
use Exception;

class FeatureVisibilityService
{
    public const HIDE_PARAM = 'hide';
    public const SHOW_PARAM = 'show';

    private const GLOBAL_UI_CONFIG_NAME = 'helpers/features';
    private const EXTENSION_NAME = 'tao';
    private const CONFIG_FILE_NAME = 'client_lib_config_registry';

    /** @var common_ext_ExtensionsManager  */
    private $extensionsManager;

    public function __construct(common_ext_ExtensionsManager $extensionsManager)
    {
        $this->extensionsManager = $extensionsManager;
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
        $taoExtension = $this->extensionsManager->getExtensionById(self::EXTENSION_NAME);
        if ($taoExtension === null) {
            throw new Exception(sprintf('Cannot find %s extension', self::EXTENSION_NAME));
        }

        $existingConfig = $taoExtension->getConfig(self::CONFIG_FILE_NAME);
        if ($existingConfig === false) {
            throw new Exception(sprintf('Cannot read %s config', self::CONFIG_FILE_NAME));
        }

        $updatedConfig = $this->{$configUpdaterCallableName}($existingConfig, $configUpdaterArg);

        $taoExtension->setConfig(self::CONFIG_FILE_NAME, $updatedConfig);
    }

    private function updateConfig(array $existingConfig, array $newStatuses): array
    {
        foreach ($newStatuses as $featureName => $featureValue) {
            if (!in_array($featureValue, [self::HIDE_PARAM, self::SHOW_PARAM], true)) {
                throw new Exception(sprintf(
                    'Feature value should be either %s or %s, %s given',
                    self::SHOW_PARAM,
                    self::HIDE_PARAM,
                    $featureValue
                ));
            }
            $existingConfig[self::GLOBAL_UI_CONFIG_NAME]['visibility'][$featureName] = $featureValue;
        }

        return $existingConfig;
    }

    private function removeFeatureFromConfig(array $existingConfig, string $featureName): array
    {
        unset($existingConfig[self::GLOBAL_UI_CONFIG_NAME]['visibility'][$featureName]);

        return $existingConfig;
    }
}
