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
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

use oat\generis\model\data\permission\PermissionHelper;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\search\index\OntologyIndexService;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\Search;

/**
 * Controller for indexed searches
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 */
class tao_actions_Search extends tao_actions_CommonModule
{
    use OntologyAwareTrait;

    /**
     * Search parameters endpoints.
     * The response provides parameters to create a datatable.
     */
    public function searchParams(): void
    {
        $rawQuery = $_POST['query'] ?? '';
        $this->returnJson([
            'url' => _url('search'),
            'params' => [
                'query' => $rawQuery,
                'rootNode' => $this->getRequestParameter('rootNode')
            ],
            'filter' => [],
            'model' => [
                OntologyRdfs::RDFS_LABEL => [
                    'id' => OntologyRdfs::RDFS_LABEL,
                    'label' => __('Label'),
                    'sortable' => false
                ]
            ],
            'result' => true
        ]);
    }

    /**
     * Search results
     * The search is paginated and initiated by the datatable component.
     *
     * @param Search    $searchService
     * @param PermissionHelper $permissionHelper
     */
    public function search(Search $searchService, PermissionHelper $permissionHelper): void
    {
        $params = $this->getRequestParameter('params');
        $query = $params['query'];
        $class = $this->getClass($params['rootNode']);

        $rows = $this->hasRequestParameter('rows') ? (int)$this->getRequestParameter('rows') : null;
        $page = $this->hasRequestParameter('page') ? (int)$this->getRequestParameter('page') : 1;
        $startRow = is_null($rows) ? 0 : $rows * ($page - 1);

        $results = [];

        // if it is an URI
        if (strpos($query, LOCAL_NAMESPACE) === 0) {
            $resource = $this->getResource($query);
            if ($resource->exists() && $resource->isInstanceOf($class)) {
                $results = new ResultSet([$resource->getUri()], 1);
            }
        }

        //  if there is no results based on considering the query as URI
        if (empty($results)) {
            $results = $searchService->query($query, $class->getUri(), $startRow, $rows);
        }

        $totalPages = is_null($rows) ? 1 : ceil($results->getTotalCount() / $rows);

        $results = $results->getArrayCopy();

        $accessibleResultsMap = array_flip(
            $permissionHelper->filterByPermission($results, PermissionInterface::RIGHT_READ)
        );

        $resultAmount = count($results);

        $response = new StdClass();
        if ($resultAmount > 0) {
            foreach ($results as $uri) {
                $instance = $this->getResource($uri);
                $isAccessible = isset($accessibleResultsMap[$uri]);

                if (!$isAccessible) {
                    $instance->label = __('Access Denied');
                }

                $instanceProperties = [
                    'id' => $instance->getUri(),
                    OntologyRdfs::RDFS_LABEL => $instance->getLabel(),
                ];

                $response->data[] = $instanceProperties;
            }
        }
        $response->readonly = array_fill_keys(
            array_keys(
                array_diff_key(
                    array_flip($results),
                    $accessibleResultsMap
                )
            ),
            true
        );
        $response->success = true;
        $response->page = empty($response->data) ? 0 : $page;
        $response->total = $totalPages;
        $response->records = $resultAmount;

        $this->returnJson($response, 200);
    }

    public function getIndexes(): void
    {
        if ($this->hasRequestParameter('rootNode') === true) {
            $rootNodeUri = $this->getRequestParameter('rootNode');
            $indexes = OntologyIndexService::getIndexesByClass($this->getClass($rootNodeUri));
            $json = [];

            foreach ($indexes as $propertyUri => $index) {
                foreach ($index as $i) {
                    $json[] = [
                        'identifier' => $i->getIdentifier(),
                        'fuzzyMatching' => $i->isFuzzyMatching(),
                        'propertyId' => $propertyUri
                    ];
                }
            }

            $this->returnJson($json, 200);
        } else {
            $this->returnJson("The 'rootNode' parameter is missing.", 500);
        }
    }
}
