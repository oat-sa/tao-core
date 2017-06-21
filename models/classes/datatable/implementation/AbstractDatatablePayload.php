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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\model\datatable\implementation;

use ArrayIterator;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\kernel\persistence\smoothsql\search\TaoResultSet;
use oat\oatbox\service\ServiceManager;
use oat\search\base\QueryBuilderInterface;
use oat\search\helper\SupportedOperatorHelper;
use oat\tao\model\datatable\DatatablePayload as DatatablePayloadInterface;
use oat\tao\model\datatable\DatatableRequest as DatatableRequestInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * class DatatablePayload
 * @package oat\tao\model\datatable
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
abstract class AbstractDatatablePayload implements DatatablePayloadInterface, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    /**
     * @var DatatableRequest
     */
    protected $request;

    /**
     * DatatablePayload constructor.
     * @param DatatableRequestInterface|null $request
     */
    public function __construct(DatatableRequestInterface $request = null)
    {
        $this->setServiceLocator(ServiceManager::getServiceManager());

        if ($request === null) {
            $request = DatatableRequest::fromGlobals();
        }

        $this->request = $request;
    }

    /**
     * Get properties map
     * Example:
     * ```php
     * [
     *    'firstname' => 'http://www.tao.lu/Ontologies/generis.rdf#userFirstName',
     *    'lastname' => 'http://www.tao.lu/Ontologies/generis.rdf#userLastName',
     * ]
     * ```
     * @return array
     */
    abstract protected function getPropertiesMap();

    /**
     * Get uri of class in which search should be done.
     * @return string
     */
    abstract protected function getType();

    /**
     * Template method to find data.
     * Any step (such as filtration, pagination, sorting e.t.c. can be changed in concrete class).
     */
    public function getPayload()
    {
        $queryBuilder = $this->getSearchService()->query();

        $this->doFiltration($queryBuilder);
        $this->doPagination($queryBuilder);
        $this->doSorting($queryBuilder);
        $searchResult = $this->doSearch($queryBuilder);
        $result = $this->doPostProcessing($searchResult);

        return $result;
    }

    /**
     * @return array
     */
    protected function getFilters()
    {
        return $this->request->getFilters();
    }

    /**
     * Apply filters to search query
     * @param QueryBuilderInterface $queryBuilder
     */
    protected function doFiltration(QueryBuilderInterface $queryBuilder)
    {
        $search = $this->getSearchService();

        $filters = $this->map($this->getFilters(), true);
        $query = $search->searchType($queryBuilder, $this->getType(), true);

        foreach ($filters as $filterProp => $filterVal) {
            foreach ($filterVal as $values) {
                if (is_array($values)) {
                    $query->addCriterion($filterProp, SupportedOperatorHelper::IN, $values);
                } elseif (is_string($values)) {
                    $query->addCriterion($filterProp, SupportedOperatorHelper::CONTAIN, $values);
                }
            }
        }

        $queryBuilder->setCriteria($query);
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     */
    protected function doPagination(QueryBuilderInterface $queryBuilder)
    {
        $rows = $this->getRows();
        $page = $this->getPage();

        if ($rows > 0) {
            $queryBuilder->setLimit($rows);
            $queryBuilder->setOffset(($page - 1) * $rows);
        }
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     */
    protected function doSorting(QueryBuilderInterface $queryBuilder)
    {
        $sortBy = $this->getSortBy();
        $sortOrder = $this->getSortOrder();
        $queryBuilder->sort($this->map([$sortBy => $sortOrder]));
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return ArrayIterator search result
     */
    protected function doSearch(QueryBuilderInterface $queryBuilder)
    {
        return $this->getSearchService()->getGateway()->search($queryBuilder);
    }

    /**
     * @param TaoResultSet $result
     * @return array
     */
    protected function doPostProcessing(TaoResultSet $result)
    {
        $payload = [
            'data' => $result->getArrayCopy(),
            'page' => (integer) $this->getPage(),
            'records' => (integer) $result->count(),
            'total' => $this->getRows() > 0
                ? ceil($result->total() / $this->getRows())
                : (integer) $result->count()
        ];

        return $this->fetchPropertyValues($payload);
    }

    /**
     * @return int
     */
    protected function getPage()
    {
        return $this->request->getPage();
    }

    /**
     * @return int
     */
    protected function getRows()
    {
        return $this->request->getRows();
    }

    /**
     * @return string
     */
    protected function getSortBy()
    {
        return $this->request->getSortBy();
    }

    /**
     * @return string
     */
    protected function getSortOrder()
    {
        return $this->request->getSortOrder();
    }

    /**
     * @return ComplexSearchService
     */
    protected function getSearchService()
    {
        return $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
    }

    /**
     * Convert array keys specified in the CSV file to keys which are used in the TAO.
     * If $reverse is `true` reverse conversion will be performed.
     *
     * Example:
     * ```php
     * $studentCsvMapper->map(['fname' => 'john']);
     * // ['http://www.tao.lu/Ontologies/generis.rdf#userFirstName' => 'john']
     * ```
     * @param array $filter
     * @param bool|string $multitask will return all filters as [[filter1], [filter2]]
     * @return array
     */
    protected function map($filter, $multitask = false)
    {
        $data = [];
        $map = $this->getPropertiesMap();
        foreach ($filter as $key => $val) {

            $key = isset($map[$key]) ? $map[$key] : $key;

            if ($multitask) {
                if (!is_array($val)) {
                    $data[$key] = [$val];
                } else {
                    $data[$key][] = array_unique($val);
                }
            } else {
                $data[$key] = $val;
            }
        }

        return $data;
    }


    /**
     * Fetch all the values of properties listed in properties map
     *
     * @param $payload
     * @return mixed
     * @throws \common_exception_InvalidArgumentType
     */
    protected function fetchPropertyValues($payload)
    {
        $propertyMap = $this->getPropertiesMap();
        $data = [];
        foreach ($payload['data'] as $resource) {
            $resource = (object)$resource;
            $resource = new \core_kernel_classes_Resource($resource->subject);
            $resourceData = $resource->getPropertiesValues($propertyMap);
            $entityInfo = array_map(function($row) use($resourceData) {
                $stringData = array_map(function($value){
                    return ($value instanceof \core_kernel_classes_Resource) ? $value->getUri() : (string) $value;
                }, $resourceData[$row]);
                return join(',', $stringData);
            }, $propertyMap);

            $entityInfo['uri'] = $resource->getUri();
            $entityInfo['id'] = \tao_helpers_Uri::encode($resource->getUri());
            $data[] = $entityInfo;
        }
        $payload['data'] = $data;

        return $payload;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getPayload();
    }
}
