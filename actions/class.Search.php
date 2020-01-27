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
 * Copyright (c) 2014-2018 (original work) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\OntologyRdfs;
use oat\tao\model\search\SyntaxException;
use oat\tao\model\search\index\OntologyIndexService;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\Search;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\search\aggregator\UnionSearchService;

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
    public function searchParams()
    {
        $rawQuery = isset($_POST['query'])?$_POST['query']:'';
        $this->returnJson(array(
            'url' => _url('search'),
            'params' => array(
                'query' => $rawQuery,
                'rootNode' => $this->getRequestParameter('rootNode')
            ),
            'filter' => array(),
            'model' => array(
                OntologyRdfs::RDFS_LABEL => array(
                    'id' => OntologyRdfs::RDFS_LABEL,
                    'label' => __('Label'),
                    'sortable' => false
                )
            ),
            'result' => true
        ));
    }

    /**
     * Search results
     * The search is paginated and initiated by the datatable component.
     */
    public function search()
    {
        $params = $this->getRequestParameter('params');
        $query = $params['query'];
        $class = $this->getClass($params['rootNode']);

        $rows = $this->hasRequestParameter('rows') ? (int)$this->getRequestParameter('rows') : null;
        $page = $this->hasRequestParameter('page') ? (int)$this->getRequestParameter('page') : 1;
        $startRow = is_null($rows) ? 0 : $rows * ($page - 1);

        try {
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
                $results = $this->getUnionSearchService()->query($query, $class->getUri(), $startRow, $rows);
            }

            $totalPages = is_null($rows) ? 1 : ceil($results->getTotalCount() / $rows);

            $response = new StdClass();
            if (count($results) > 0) {
                foreach ($results as $uri) {
                    $instance = $this->getResource($uri);
                    $instanceProperties = array(
                        'id' => $instance->getUri(),
                        OntologyRdfs::RDFS_LABEL => $instance->getLabel()
                    );

                    $response->data[] = $instanceProperties;
                }
            }
            $response->success = true;
            $response->page = empty($response->data) ? 0 : $page;
            $response->total = $totalPages;
            $response->records = count($results);

            $this->returnJson($response, 200);
        } catch (SyntaxException $e) {
            $this->returnJson(array(
                'success' => false,
                'msg' => $e->getUserMessage()
            ));
        }
    }

    public function getIndexes()
    {
        if ($this->hasRequestParameter('rootNode') === true) {
            $rootNodeUri = $this->getRequestParameter('rootNode');
            $indexes = OntologyIndexService::getIndexesByClass($this->getClass($rootNodeUri));
            $json = array();

            foreach ($indexes as $propertyUri => $index) {
                foreach ($index as $i) {
                    $json[] = array(
                        'identifier' => $i->getIdentifier(),
                        'fuzzyMatching' => $i->isFuzzyMatching(),
                        'propertyId' => $propertyUri
                    );
                }
            }

            $this->returnJson($json, 200);
        } else {
            $this->returnJson("The 'rootNode' parameter is missing.", 500);
        }
    }

    /**
     * @return UnionSearchService
     */
    protected function getUnionSearchService() : UnionSearchService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(UnionSearchService::SERVICE_ID);
    }
}
