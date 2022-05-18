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

class FeatureVisibilityService
{
    public const HIDE_PARAM = 'hide';
    public const SHOW_PARAM = 'show';

    private const GLOBAL_UI_CONFIG_NAME = 'services/features';

    /** @var AbstractRegistry */
    private $registry;

    public function __construct(ClientLibConfigRegistry $registry)
    {
        $this->registry = $registry::getRegistry();
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
