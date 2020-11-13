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

use Exception;
use oat\generis\model\data\permission\PermissionHelper;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use Psr\Http\Message\ServerRequestInterface;

class SearchProxy extends ConfigurableService
{
    use OntologyAwareTrait;

    /**
     * @throws Exception
     */
    public function search(ServerRequestInterface $request): array
    {
        $query = $this->getQueryFactory()->createSearchQuery($request);

        if ($this->getElasticSearchChecker()->isEnabled()) {
            $results = $this->getElasticSearchBridge()->search($query);
        }

        if (!$this->getElasticSearchChecker()->isEnabled()) {
            $results = $this->getGenerisSearchBridge()->search($query);
        }

        if (!$results instanceof ResultSet) {
            throw new Exception('Result has to be instance of ResultSet');
        }

        $rows = isset($request->getQueryParams()['rows']) ? (int) $request->getQueryParams()['rows'] : null;
        $page = isset($request->getQueryParams()['page']) ? (int)$request->getQueryParams()['page'] : 1;

        $totalPages = is_null($rows) ? 1 : ceil($results->getTotalCount() / $rows);

        $resultsRaw = $results->getArrayCopy();

        $accessibleResultsMap = [];

        $resultAmount = count($resultsRaw);

        $response = [];
        if ($resultAmount > 0) {
            $accessibleResultsMap = array_flip(
                $this->getPermissionHelper()->filterByPermission($resultsRaw, PermissionInterface::RIGHT_READ)
            );

            foreach ($resultsRaw as $uri) {
                $instance = $this->getResource($uri);
                $isAccessible = isset($accessibleResultsMap[$uri]);

                if (!$isAccessible) {
                    $instance->label = __('Access Denied');
                }

                $instanceProperties = [
                    'id' => $instance->getUri(),
                    OntologyRdfs::RDFS_LABEL => $instance->getLabel(),
                ];

                $response['data'][] = $instanceProperties;
            }
        }
        $response['readonly'] = array_fill_keys(
            array_keys(
                array_diff_key(
                    array_flip($resultsRaw),
                    $accessibleResultsMap
                )
            ),
            true
        );
        $response['success'] = true;
        $response['page'] = empty($response['data']) ? 0 : $page;
        $response['total'] = $totalPages;

        $response['totalCount'] = $results->getTotalCount();

        $response['records'] = $resultAmount;

        return $response;
    }

    private function getPermissionHelper(): PermissionHelper
    {
        return $this->getServiceLocator()->get(PermissionHelper::class);
    }

    private
    function getElasticSearchChecker(): AdvancedSearchChecker
    {
        return $this->getServiceLocator()->get(AdvancedSearchChecker::class);
    }

    private
    function getElasticSearchBridge(): ElasticSearchBridge
    {
        return $this->getServiceLocator()->get(ElasticSearchBridge::class);
    }

    private
    function getGenerisSearchBridge(): GenerisSearchBridge
    {
        return $this->getServiceLocator()->get(GenerisSearchBridge::class);
    }

    private
    function getQueryFactory(): SearchQueryFactory
    {
        return $this->getServiceLocator()->get(SearchQueryFactory::class);
    }
}