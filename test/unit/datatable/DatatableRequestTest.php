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

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use oat\tao\model\datatable\implementation\DatatableRequest;
use Psr\Http\Message\ServerRequestInterface;

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
     * @dataProvider requestsProvider
     * @param ServerRequestInterface $request
     * @param array $result expected results
     */
    public function testGetRows(ServerRequestInterface $request, array $result): void
    {
        $datatableRequest = new DatatableRequest($request);
        $this->assertEquals($result['rows'], $datatableRequest->getRows());
    }

    /**
     * @dataProvider requestsProvider
     * @param ServerRequestInterface $request
     * @param array $result expected results
     */
    public function testGetPage(ServerRequestInterface $request, array $result): void
    {
        $datatableRequest = new DatatableRequest($request);
        $this->assertEquals($result['page'], $datatableRequest->getPage());
    }

    /**
     * @dataProvider requestsProvider
     * @param ServerRequestInterface $request
     * @param array $result expected results
     */
    public function testGetSortBy(ServerRequestInterface $request, array $result): void
    {
        $datatableRequest = new DatatableRequest($request);
        $this->assertEquals($result['sortby'], $datatableRequest->getSortBy());
    }

    /**
     * @dataProvider requestsProvider
     * @param ServerRequestInterface $request
     * @param array $result expected results
     */
    public function testGetSortOrder(ServerRequestInterface $request, array $result): void
    {
        $datatableRequest = new DatatableRequest($request);
        $this->assertEquals($result['sortorder'], $datatableRequest->getSortOrder());
    }

    /**
     * @dataProvider requestsProvider
     * @param ServerRequestInterface $request
     * @param array $result expected results
     */
    public function testGetFilters(ServerRequestInterface $request, array $result): void
    {
        $datatableRequest = new DatatableRequest($request);
        $this->assertEquals($result['filters'], $datatableRequest->getFilters());
    }

    public function requestsProvider(): array
    {
        $baseUri = 'http://localhost';
        return [
            [
                (new ServerRequest('GET', $baseUri))->withQueryParams([
                    'rows' => '5',
                    'page' => '1',
                    'sortby' => 'id',
                    'sortorder' => 'asc',
                    'filtercolumns' => ['lastname' => 'John'],
                ]),
                [
                    'rows' => 5,
                    'page' => 1,
                    'sortby' => 'id',
                    'sortorder' => 'asc',
                    'filters' => ['lastname' => 'John'],
                ],
            ],
            [
                (new ServerRequest('GET', $baseUri))->withQueryParams([
                    'rows' => '5',
                    'page' => '1',
                    'sortby' => 'id',
                    'sortorder' => 'DESC',
                    'filtercolumns' => ['lastname' => 'John', 'firstname' => 'Doe'],
                ]),
                [
                    'rows' => 5,
                    'page' => 1,
                    'sortby' => 'id',
                    'sortorder' => 'desc',
                    'filters' => ['lastname' => 'John', 'firstname' => 'Doe'],
                ],
            ],
            [
                (new ServerRequest('GET', $baseUri))->withQueryParams([
                    'rows' => 25,
                    'sortby' => 'id',
                ]),
                [
                    'rows' => 25,
                    'page' => 1,
                    'sortby' => 'id',
                    'sortorder' => 'asc',
                    'filters' => [],
                ],
            ],
            [
                (new ServerRequest('GET', $baseUri))->withQueryParams([
                    'rows' => 25,
                    'page' => 2,
                    'sortby' => 'id',
                    'sortorder' => 'abc',
                ]),
                [
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
