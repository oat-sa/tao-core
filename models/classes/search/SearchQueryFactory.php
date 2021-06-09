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

namespace oat\tao\model\search;

use oat\oatbox\service\ConfigurableService;
use Psr\Http\Message\ServerRequestInterface;

class SearchQueryFactory extends ConfigurableService
{
    /**
     * @throws CreateSearchQueryException
     */
    public function create(ServerRequestInterface $request): SearchQuery
    {
        $params = $request->getQueryParams();

        $this->checkRequiredParams($params);

        $rows = isset($params['rows']) ? (int)$params['rows'] : null;
        $page = isset($params['page']) ? (int)$params['page'] : null;
        $startRow = is_null($rows) ? 0 : $rows * ($page - 1);

        return new SearchQuery(
            $params['params']['query'],
            $params['params']['structure'],
            $params['params']['parentNode'],
            $startRow,
            $rows,
            $page
        );
    }

    /**
     * @throws CreateSearchQueryException
     */
    private function checkRequiredParams(array $params): void
    {
        if (!isset($params['params']['rootNode'])) {
            throw new CreateSearchQueryException('Root node is missing from request');
        }

        if (!isset($params['params']['query'])) {
            throw new CreateSearchQueryException('User input is missing');
        }
    }
}
