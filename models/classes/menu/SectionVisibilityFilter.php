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
use oat\oatbox\user\UserInterfaceModeService;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use tao_models_classes_UserService;

class SectionVisibilityFilter extends ConfigurableService implements SectionVisibilityFilterInterface
{
    public const SERVICE_ID = 'tao/SectionVisibilityFilter';
    public const OPTION_FEATURE_FLAG_SECTIONS = 'featureFlagSections';
    public const OPTION_FEATURE_FLAG_SECTIONS_TO_HIDE = 'featureFlagSectionsToHide';

    //TODO:: Add sections hidden for simple mode
    public const SIMPLE_MODE_HIDDEN_SECTIONS = [];

    /**
     * @throws LogicException
     */
    public function isVisible(string $section): bool
    {
        $sections = $this->getOption(self::OPTION_FEATURE_FLAG_SECTIONS, []);
        $sectionToHide = $this->getOption(self::OPTION_FEATURE_FLAG_SECTIONS_TO_HIDE, []);

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

        if ($this->getUserInterfaceMode() == GenerisRdf::PROPERTY_USER_INTERFACE_MODE_SIMPLE) {
            if (in_array($section, self::SIMPLE_MODE_HIDDEN_SECTIONS)) {
                return false;
            }
        }

        return true;
    }
    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getServiceLocator()->get(FeatureFlagChecker::class);
    }

    private function getUserInterfaceModeService(): UserInterfaceModeService
    {
        return $this->getServiceLocator()->getContainer()->get(UserInterfaceModeService::class);
    }

    private function getUserService(): tao_models_classes_UserService
    {
        return $this->getServiceLocator()->get(tao_models_classes_UserService::class);
    }

    private function getUserInterfaceMode(): string
    {
        return $this->getUserInterfaceModeService()->getUserInterfaceMode(
            $this->getUserService()->getCurrentUser()
        );
    }
}
