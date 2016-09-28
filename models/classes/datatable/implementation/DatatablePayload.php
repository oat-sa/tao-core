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

use oat\tao\model\datatable\DatatablePayload as DatatablePayloadInterface;
use oat\tao\model\datatable\DatatableRequest as DatatableRequestInterface;
use oat\tao\model\datatable\implementation\DatatableRequest;
use oat\oatbox\service\ServiceManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\helper\SupportedOperatorHelper;

/**
 * Interface DatatablePayload
 * @package oat\tao\model\datatable
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
abstract class DatatablePayload implements DatatablePayloadInterface, ServiceLocatorAwareInterface
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
     *    'http://www.tao.lu/Ontologies/generis.rdf#userFirstName' => 'firstname'
     *    'http://www.tao.lu/Ontologies/generis.rdf#userLastName' => 'lastname'
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
     * Template method to find data
     */
    public function getPayload()
    {
        $filters = $this->map($this->getFilters());
        $page = $this->getPage();
        $rows = $this->getRows();
        $sortBy = $this->getSortBy();
        $sortOrder = $this->getSortOrder();

        $search = $this->getSearchService();

        $queryBuilder = $search->query();
        $query = $search->searchType($queryBuilder, $this->getType(), true);

        foreach ($filters as $filterProp => $filterVal) {
            if (is_string($filterVal)) {
                $query->addCriterion($filterProp, SupportedOperatorHelper::CONTAIN, $filterVal);
            } else if (is_array($filterVal)) {
                $query->addCriterion($filterProp, SupportedOperatorHelper::IN, $filterVal);
            }
        }

        $query->sort($this->map([$sortBy => $sortOrder]));
        $query->setLimit($rows);
        $query->setOffset(($page - 1) * $rows);

        $queryBuilder->setCriteria($query);

        $result = $search->getGateway()->search($queryBuilder);

        return [
            'data' => $result,
            'page' => $page,
            'records' => $result->count(),
            'total' => $result->total(),
        ];
    }

    /**
     * @return array
     */
    protected function getFilters()
    {
        return $this->request->getFilters();
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
     *
     * Elements which is not represented in the static::$map array will be removed.
     *
     * @param array $filter
     * @return array
     */
    protected function map($filter)
    {
        $data = [];

        foreach ($filter as $key => $val) {
            $newKey = array_search($key, $this->getPropertiesMap());

            if ($newKey !== false) {
                $data[$newKey] = $val;
            } else {
                $data[$key] = $val;
            }
        }

        return $data;
    }

    
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
    
}
