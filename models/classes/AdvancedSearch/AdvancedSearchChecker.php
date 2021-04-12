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

namespace oat\tao\model\AdvancedSearch;

use oat\tao\model\search\Search;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;

class AdvancedSearchChecker extends ConfigurableService
{
    public const FEATURE_FLAG_ADVANCED_SEARCH_DISABLED = 'FEATURE_FLAG_ADVANCED_SEARCH_DISABLED';

    public const OPTION_ALLOWED_SEARCH_CLASSES = 'allowedSearchClasses';

    public function isEnabled(): bool
    {
        return $this->isAdvancedSearchEnabled() && $this->isElasticSearchEnabled();
    }

    private function isAdvancedSearchEnabled(): bool
    {
        return false;
        //FIXME @TODO Remove after testing
        return !$this->getFeatureFlagChecker()->isEnabled(self::FEATURE_FLAG_ADVANCED_SEARCH_DISABLED);
    }

    private function isElasticSearchEnabled(): bool
    {
        $allowedSearchClasses = (array) $this->getOption(
            self::OPTION_ALLOWED_SEARCH_CLASSES,
            ['oat\tao\elasticsearch\ElasticSearch']
        );

        return in_array(get_class($this->getSearchService()), $allowedSearchClasses, true);
    }

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getServiceLocator()->get(FeatureFlagChecker::class);
    }

    private function getSearchService(): Search
    {
        return $this->getServiceLocator()->get(Search::SERVICE_ID);
    }
}
