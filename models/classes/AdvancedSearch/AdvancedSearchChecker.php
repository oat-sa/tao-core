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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\AdvancedSearch;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\search\SearchInterface;
use oat\tao\model\search\SearchProxy;
use common_ext_ExtensionsManager;

class AdvancedSearchChecker extends ConfigurableService
{
    /** @deprecated Use oat\tao\model\featureFlag\FeatureFlagCheckerInterface::FEATURE_FLAG_ADVANCED_SEARCH_DISABLED */
    public const FEATURE_FLAG_ADVANCED_SEARCH_DISABLED = FeatureFlagCheckerInterface::FEATURE_FLAG_ADVANCED_SEARCH_DISABLED;

    public const CONFIG_ADVANCED_SEARCH_DISABLED_SECTIONS = 'TAO_ADVANCED_SEARCH_DISABLED_SECTIONS';

    public function isEnabled(): bool
    {
        return !$this->getFeatureFlagChecker()->isEnabled(FeatureFlagCheckerInterface::FEATURE_FLAG_ADVANCED_SEARCH_DISABLED)
            && $this->getSearchService()->supportCustomIndex();
    }

    public function getDisabledSections(): array
    {
        $disabledSections = [
            'results',
        ];

        if ($this->getExtensionsManager()->isEnabled('taoBooklet')) {
            $disabledSections[] = 'taoBooklet_main';
        }

        if (isset($_ENV[self::CONFIG_ADVANCED_SEARCH_DISABLED_SECTIONS])) {
            $conf = $_ENV[self::CONFIG_ADVANCED_SEARCH_DISABLED_SECTIONS];

            foreach (explode(',', $conf) as $section) {
                $section = trim($section);
                if ($section != '' && !in_array($section, $disabledSections)) {
                    $disabledSections[] = $section;
                }
            }
        }

        return $disabledSections;
    }

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getServiceLocator()->get(FeatureFlagChecker::class);
    }

    private function getSearchService(): SearchInterface
    {
        return $this->getServiceLocator()->get(SearchProxy::SERVICE_ID);
    }

    private function getExtensionsManager(): common_ext_ExtensionsManager
    {
        return $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
    }
}
