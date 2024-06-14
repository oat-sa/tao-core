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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\menu;

use LogicException;
use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\user\implementation\UserSettingsService;
use oat\tao\model\user\UserSettingsInterface;
use oat\tao\model\user\UserSettingsServiceInterface;

class SectionVisibilityFilter extends ConfigurableService implements SectionVisibilityFilterInterface
{
    public const SERVICE_ID = 'tao/SectionVisibilityFilter';
    public const OPTION_FEATURE_FLAG_SECTIONS = 'featureFlagSections';
    public const OPTION_FEATURE_FLAG_SECTIONS_TO_HIDE = 'featureFlagSectionsToHide';
    private const DEFAULT_FEATURE_FLAG_SECTIONS_TO_HIDE = [
        'settings_my_password' => [
            FeatureFlagCheckerInterface::FEATURE_FLAG_SOLAR_DESIGN_ENABLED
        ]
    ];

    public const SIMPLE_INTERFACE_MODE_HIDDEN_SECTIONS = [];

    /**
     * @throws LogicException
     */
    public function isVisible(string $section): bool
    {
        $sections = $this->getOption(self::OPTION_FEATURE_FLAG_SECTIONS, []);
        $sectionToHide = array_merge_recursive(
            $this->getOption(self::OPTION_FEATURE_FLAG_SECTIONS_TO_HIDE, []),
            self::DEFAULT_FEATURE_FLAG_SECTIONS_TO_HIDE
        );

        foreach ($sectionToHide[$section] ?? [] as $featureFlag) {
            if ($this->getFeatureFlagChecker()->isEnabled($featureFlag)) {
                return false;
            }
        }

        foreach ($sections[$section] ?? [] as $featureFlag) {
            if (!$this->getFeatureFlagChecker()->isEnabled($featureFlag)) {
                return false;
            }
        }

        $userSettings = $this->getUserSettingsService()->getCurrentUserSettings();

        if (
            $userSettings->getSetting(
                UserSettingsInterface::INTERFACE_MODE
            ) === GenerisRdf::PROPERTY_USER_INTERFACE_MODE_SIMPLE
            && in_array($section, self::SIMPLE_INTERFACE_MODE_HIDDEN_SECTIONS)
        ) {
            return false;
        }

        return true;
    }

    public function hideSectionByFeatureFlag(string $section, string $featureFlag): void
    {
        $options = $this->getOption(SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS_TO_HIDE, []);
        $options[$section] = array_merge(
            $options[$section] ?? [],
            [
                $featureFlag
            ]
        );
        $this->setOption(SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS_TO_HIDE, $options);
    }

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getServiceManager()->getContainer()->get(FeatureFlagChecker::class);
    }

    private function getUserSettingsService(): UserSettingsServiceInterface
    {
        return $this->getServiceManager()->getContainer()->get(UserSettingsService::class);
    }
}
