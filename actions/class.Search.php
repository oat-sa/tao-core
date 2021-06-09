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

use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\tao\model\search\index\OntologyIndexService;
use oat\tao\model\search\ResultSetMapper;
use oat\tao\model\search\SearchProxy;

/**
 * Controller for indexed searches
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 */
class tao_actions_Search extends tao_actions_CommonModule
{
    use OntologyAwareTrait;
    use HttpJsonResponseTrait;

    /**
     * Search parameters endpoints.
     * The response provides parameters to create a datatable.
     */
    public function searchParams(): void
    {
        $queryParams = $this->getPsrRequest()->getQueryParams();
        $parsedBody = $this->getPsrRequest()->getParsedBody();
        if (
            !isset(
                $parsedBody['structure'],
                $parsedBody['query'],
                $queryParams['rootNode'],
                $parsedBody['parentNode']
            )
        ) {
            $this->setErrorJsonResponse('Request is missing required params');
        }

        try {
            $promiseModel = $this->getResultSetMapper()->map($parsedBody['structure']);
        } catch (Exception $exception) {
            $this->setErrorJsonResponse($exception->getMessage());
            return;
        }

        $this->setSuccessJsonResponse([
            'url' => _url('search'),
            'params' => [
                'query' => $parsedBody['query'],
                'rootNode' => $queryParams['rootNode'],
                'parentNode' => $parsedBody['parentNode'],
                'structure' => $parsedBody['structure'],
            ],
            'filter' => [],
            'model' => $promiseModel,
            'result' => true
        ]);
    }

    /**
     * Search results
     * The search is paginated and initiated by the datatable component.
     */
    public function search(): void
    {
        try {
            $this->returnJson(
                $this->getSearchProxy()->search(
                    $this->getPsrRequest()
                )
            );
        } catch (Exception $exception) {
            $this->setErrorJsonResponse(
                $exception->getMessage()
            );
        }
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
                        'propertyId' => $propertyUri,
                    ];
                }
            }

            $this->setSuccessJsonResponse($json, 200);
        } else {
            $this->returnJson("The 'rootNode' parameter is missing.", 500);
        }
    }

    private function getSearchProxy(): SearchProxy
    {
        return $this->getServiceLocator()->get(SearchProxy::SERVICE_ID);
    }

    private function getResultSetMapper(): ResultSetMapper
    {
        return $this->getServiceLocator()->get(ResultSetMapper::SERVICE_ID);
    }
}
