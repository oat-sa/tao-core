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
namespace oat\tao\test\datatable;

use oat\tao\model\datatable\implementation\AbstractDatatablePayload;
use oat\tao\model\datatable\implementation\DatatableRequest;
use oat\tao\test\TaoPhpUnitTestRunner;
use Slim\Http\Environment;
use Slim\Http\Request;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use Prophecy\Argument;

/**
 * Class DatatablePayloadTest
 * @package oat\tao\test\datatable
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class DatatablePayloadTest extends TaoPhpUnitTestRunner
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @dataProvider environmentsProvider
     * @preserveGlobalState disabled
     *
     * @param Environment $env
     */
    public function testGetPayload($env)
    {
        $request = Request::createFromEnvironment($env);
        $datatableRequest = new DatatableRequest($request);

        $datatablePayload = new ConcreteDatatablePayload($datatableRequest);
        $datatablePayload->setSearchService($this->getSearchServiceMock($datatableRequest));

        $payload = $datatablePayload->getPayload();
        $this->assertTrue(is_array($payload));
    }


    public function environmentsProvider()
    {
        return [
            [
                'env' => Environment::mock([
                    'QUERY_STRING' => http_build_query([
                        'rows' => '5',
                        'page' => '1',
                        'sortby' => 'id',
                        'sortorder' => 'asc',
                        'filtercolumns' => ['lastname' => 'John'],
                    ]),
                    'REQUEST_METHOD' => 'GET',
                ]),
            ],
        ];
    }

    /**
     * @param DatatableRequest $datatableRequest
     * @return ComplexSearchService
     */
    protected function getSearchServiceMock(DatatableRequest $datatableRequest)
    {
        $filters = $datatableRequest->getFilters();

        $queryProphecy = $this->prophesize('\oat\tao\test\datatable\QueryMock');
        $queryProphecy->addCriterion(
            Argument::type('string'),
            Argument::type('string'),
            Argument::any()
        )->shouldBeCalledTimes(count($filters));

        $queryMock = $queryProphecy->reveal();

        $queryBuilderProphecy = $this->prophesize('\oat\search\QueryBuilder');
        $queryBuilderProphecy->setCriteria($queryMock)->shouldBeCalledTimes(count($filters));
        $queryBuilderProphecy->sort(Argument::any())->shouldBeCalledTimes(1);
        $queryBuilderProphecy->setLimit(Argument::type('integer'))->shouldBeCalledTimes(1);
        $queryBuilderProphecy->setOffset(Argument::type('integer'))->shouldBeCalledTimes(1);
        $queryBuilderMock = $queryBuilderProphecy->reveal();


        $resultProphecy = $this->prophesize('\oat\generis\model\kernel\persistence\smoothsql\search\TaoResultSet');
        $resultProphecy->count()->willReturn(1)->shouldBeCalledTimes(1);
        $resultProphecy->total()->willReturn(2)->shouldBeCalledTimes(1);
        $resultProphecy->getArrayCopy()->willReturn([])->shouldBeCalledTimes(1);
        $result = $resultProphecy->reveal();

        $gatewayProphecy = $this->prophesize('\oat\search\TaoSearchGateWay');
        $gatewayProphecy->search($queryBuilderMock)->willReturn($result)->shouldBeCalledTimes(1);
        $gatewayMock = $gatewayProphecy->reveal();

        $service = $this->prophesize('\oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService');
        $service->query()->willReturn($queryBuilderMock)->shouldBeCalledTimes(1);
        $service->getGateway()->willReturn($gatewayMock)->shouldBeCalledTimes(1);
        $service->searchType($queryBuilderMock, Argument::type('string'), true)->shouldBeCalledTimes(1)->willReturn($queryMock);

        return $service->reveal();
    }
}


class ConcreteDatatablePayload extends AbstractDatatablePayload
{
    protected $searchService;

    public function setSearchService($searchService)
    {
        $this->searchService = $searchService;
    }

    protected function getSearchService()
    {
        return $this->searchService;
    }

    /**
     * @return array
     */
    public function getPropertiesMap()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getType()
    {
        return 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject';
    }
}

class QueryMock extends \oat\search\Query
{
    public function sort(){}
    public function setLimit(){}
    public function setOffset(){}
}
