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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\tao\model\search;

use oat\tao\model\menu\MenuService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\search\index\IndexIterator;
use oat\tao\model\search\index\IndexService;

/**
 * Search service
 * 
 * @author Joel Bout <joel@taotesting.com>
 * @deprecated
 */
class SearchService
{	
    const CONFIG_KEY = 'search';

    /**
     * @return Search
     */
    static public function getSearchImplementation() 
    {
        return ServiceManager::getServiceManager()->get(Search::SERVICE_ID);
    }

    /**
     * Store the search implementation to use
     * 
     * @param Search $impl
     */
    static public function setSearchImplementation(Search $impl) 
    {
        return ServiceManager::getServiceManager()->register(Search::SERVICE_ID, $impl);
    }
    
    /**
     * Runs a full reindexing of the resources
     * 
     * @return int nr of resources indexed
     */
    static public function runIndexing() 
    {
        $indexService = ServiceManager::getServiceManager()->get(IndexService::SERVICE_ID);
        return $indexService->runIndexing();
    }
}
