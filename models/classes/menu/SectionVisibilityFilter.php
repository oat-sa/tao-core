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
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\featureFlag\FeatureFlagChecker;

class SectionVisibilityFilter extends ConfigurableService implements SectionVisibilityFilterInterface
{
    public const SERVICE_ID = 'tao/SectionVisibilityFilter';
    public const OPTION_FEATURE_FLAG_SECTIONS = 'featureFlagSections';

    /**
     * @throws LogicException
     */
    public function isHidden(string $section): bool
    {
        $sections = $this->getOption(self::OPTION_FEATURE_FLAG_SECTIONS);

        if (empty($sections[$section])) {
            return false;
        }

        foreach ($sections as $featureFlags) {
            $this->checkFeatureFlags($featureFlags);
        }

        return false;
    }

    /**
     * @param string[]
     */
    private function checkFeatureFlags(array $featureFlags): bool
    {
        foreach ($featureFlags as $featureFlag){
            if (!$this->getFeatureFlagChecker()->isEnabled($featureFlag)) {
                return true;
            }
        }

        return false;
    }

    private function getFeatureFlagChecker(): FeatureFlagChecker
    {
        return $this->getServiceLocator()->get(FeatureFlagChecker::class);
    }
}
