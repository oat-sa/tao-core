<?php

declare(strict_types=1);

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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\search;

use oat\oatbox\service\ServiceManager;
use oat\tao\model\search\index\IndexService;

/**
 * Search service
 *
 * @author Joel Bout <joel@taotesting.com>
 * @deprecated
 */
class SearchService
{
    public const CONFIG_KEY = 'search';

    /**
     * @return Search
     */
    public static function getSearchImplementation()
    {
        return ServiceManager::getServiceManager()->get(Search::SERVICE_ID);
    }

    /**
     * Store the search implementation to use
     *
     * @param Search $impl
     */
    public static function setSearchImplementation(Search $impl)
    {
        return ServiceManager::getServiceManager()->register(Search::SERVICE_ID, $impl);
    }

    /**
     * Runs a full reindexing of the resources
     *
     * @return int nr of resources indexed
     */
    public static function runIndexing()
    {
        $indexService = ServiceManager::getServiceManager()->get(IndexService::SERVICE_ID);
        return $indexService->runIndexing();
    }
}
