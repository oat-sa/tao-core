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

namespace oat\tao\test\unit\datatable;

use PHPUnit\Framework\TestCase;
use oat\tao\model\datatable\implementation\DatatableRequest;
use Slim\Http\Environment;
use Slim\Http\Request;

/**
 * Class DatatableRequestTest
 * @package oat\tao\test\unit\datatable
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class DatatableRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @dataProvider environmentsProvider
     * @preserveGlobalState disabled
     *
     * @param Environment $env
     * @param array $result expected results
     */
    public function testGetRows($env, $result)
    {
        $request = Request::createFromEnvironment($env);
        $datatableRequest = new DatatableRequest($request);

        $this->assertEquals($result['rows'], $datatableRequest->getRows());
    }

    /**
     * @dataProvider environmentsProvider
     * @preserveGlobalState disabled
     *
     * @param Environment $env
     * @param array $result expected results
     */
    public function testGetPage($env, $result)
    {
        $request = Request::createFromEnvironment($env);
        $datatableRequest = new DatatableRequest($request);

        $this->assertEquals($result['page'], $datatableRequest->getPage());
    }

    /**
     * @dataProvider environmentsProvider
     * @preserveGlobalState disabled
     *
     * @param Environment $env
     * @param array $result expected results
     */
    public function testGetSortBy($env, $result)
    {

        $request = Request::createFromEnvironment($env);
        $datatableRequest = new DatatableRequest($request);

        $this->assertEquals($result['sortby'], $datatableRequest->getSortBy());
    }

    /**
     * @dataProvider environmentsProvider
     * @preserveGlobalState disabled
     *
     * @param Environment $env
     * @param array $result expected results
     */
    public function testGetSortOrder($env, $result)
    {

        $request = Request::createFromEnvironment($env);
        $datatableRequest = new DatatableRequest($request);

        $this->assertEquals($result['sortorder'], $datatableRequest->getSortOrder());
    }

    /**
     * @dataProvider environmentsProvider
     * @preserveGlobalState disabled
     *
     * @param Environment $env
     * @param array $result expected results
     */
    public function testGetFilters($env, $result)
    {

        $request = Request::createFromEnvironment($env);
        $datatableRequest = new DatatableRequest($request);

        $this->assertEquals($result['filters'], $datatableRequest->getFilters());
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
                'result' => [
                    'rows' => 5,
                    'page' => 1,
                    'sortby' => 'id',
                    'sortorder' => 'asc',
                    'filters' => ['lastname' => 'John'],
                ],
            ],
            [
                'env' => Environment::mock([
                    'QUERY_STRING' => http_build_query([
                        'rows' => '5',
                        'page' => '1',
                        'sortby' => 'id',
                        'sortorder' => 'DESC',
                        'filtercolumns' => ['lastname' => 'John', 'firstname' => 'Doe'],
                    ]),
                    'REQUEST_METHOD' => 'GET',
                ]),
                'result' => [
                    'rows' => 5,
                    'page' => 1,
                    'sortby' => 'id',
                    'sortorder' => 'desc',
                    'filters' => ['lastname' => 'John', 'firstname' => 'Doe'],
                ],
            ],
            [
                'env' => Environment::mock([
                    'QUERY_STRING' => http_build_query([
                        'rows' => 25,
                        'sortby' => 'id',
                    ]),
                    'REQUEST_METHOD' => 'GET',
                ]),
                'result' => [
                    'rows' => 25,
                    'page' => 1,
                    'sortby' => 'id',
                    'sortorder' => 'asc',
                    'filters' => [],
                ],
            ],
            [
                'env' => Environment::mock([
                    'QUERY_STRING' => http_build_query([
                        'rows' => 25,
                        'page' => 2,
                        'sortby' => 'id',
                        'sortorder' => 'abc',
                    ]),
                    'REQUEST_METHOD' => 'GET',
                ]),
                'result' => [
                    'rows' => 25,
                    'page' => 2,
                    'sortby' => 'id',
                    'sortorder' => 'asc',
                    'filters' => [],
                ],
            ],
        ];
    }
}
