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

use Psr\Container\ContainerInterface;
use oat\tao\model\search\SearchProxy;
use oat\tao\elasticsearch\ElasticSearch;
use oat\tao\model\search\SearchInterface;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;

class AdvancedSearchChecker extends ConfigurableService
{
    public function isEnabled(): bool
    {
        return !$this->getFeatureFlagChecker()->isEnabled(FeatureFlagCheckerInterface::FEATURE_FLAG_ADVANCED_SEARCH_DISABLED)
            && $this->getSearchService()->supportCustomIndex();
    }

    public function ping(): bool
    {
        $advancedSearch = $this->getSearchService()->getAdvancedSearch();

        return $advancedSearch instanceof ElasticSearch && $advancedSearch->ping();
    }

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getContainer()->get(FeatureFlagChecker::class);
    }

    /**
     * @return SearchInterface|SearchProxy
     */
    private function getSearchService(): SearchInterface
    {
        return $this->getContainer()->get(SearchProxy::SERVICE_ID);
    }

    private function getContainer(): ContainerInterface
    {
        return $this->getServiceManager()->getContainer();
    }
}
