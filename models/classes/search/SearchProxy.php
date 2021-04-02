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

declare(strict_types=1);

namespace oat\tao\model\search;

use Exception;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use Psr\Http\Message\ServerRequestInterface;

class SearchProxy extends ConfigurableService
{
    use OntologyAwareTrait;

    private const GENERIS_SEARCH_WHITELIST = [
        'http://www.tao.lu/Ontologies/generis.rdf#ClassRole'
    ];

    /**
     * @throws Exception
     */
    public function search(ServerRequestInterface $request): array
    {
        $query = $this->getQueryFactory()->create($request);
        $queryParams = $request->getQueryParams();
        $results = $this->executeSearch($query);

        if (!$results instanceof ResultSet) {
            throw new Exception('Result has to be instance of ResultSet');
        }

        return $this->getResultSetResponseNormalizer()
            ->normalize($query, $results, $queryParams['params']['structure']);
    }

    private function executeSearch(SearchQuery $query): ResultSet
    {
        if (in_array($query->getParentClass(), self::GENERIS_SEARCH_WHITELIST)) {
            return $this->getGenerisSearchBridge()->search($query);
        }

        if ($this->getElasticSearchChecker()->isEnabled()) {
            return $this->getElasticSearchBridge()->search($query);
        }

        return $this->getGenerisSearchBridge()->search($query);
    }

    private function getResultSetResponseNormalizer(): ResultSetResponseNormalizer
    {
        return $this->getServiceLocator()->get(ResultSetResponseNormalizer::class);
    }

    private function getElasticSearchChecker(): AdvancedSearchChecker
    {
        return $this->getServiceLocator()->get(AdvancedSearchChecker::class);
    }

    private function getElasticSearchBridge(): ElasticSearchBridge
    {
        return $this->getServiceLocator()->get(ElasticSearchBridge::class);
    }

    private function getGenerisSearchBridge(): GenerisSearchBridge
    {
        return $this->getServiceLocator()->get(GenerisSearchBridge::class);
    }

    private function getQueryFactory(): SearchQueryFactory
    {
        return $this->getServiceLocator()->get(SearchQueryFactory::class);
    }
}
