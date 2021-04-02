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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\search;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\strategy\GenerisSearch;

class GenerisSearchBridge extends ConfigurableService implements SearchBridgeInterface
{
    public function search(SearchQuery $query): ResultSet
    {
        return $this->getSearchService()->query(
            $query->getTerm(),
            $query->getParentClass(),
            $query->getStartRow(),
            $query->getRows()
        );
    }

    private function getSearchService(): Search
    {
        $search = $this->getServiceLocator()->get(Search::SERVICE_ID);

        if (!$search instanceof GenerisSearch) {
            $search = new GenerisSearch();
            $search->setServiceManager($this->getServiceManager());
        }

        return $search;
    }
}
