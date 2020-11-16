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

namespace oat\tao\test\unit\model\search;

use oat\tao\model\search\CreateSearchQueryException;
use oat\tao\model\search\SearchQueryFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class SearchQueryFactoryTest extends TestCase
{
    /** @var SearchQueryFactory */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new SearchQueryFactory();
    }

    public function testCreateSearchQuery(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getQueryParams')
            ->willReturn(
                [
                    'params' =>
                        [
                            'query' => 'exampleQuery',
                            'rootNode' => 'exampleRootNode',
                            'parentNode' => 'exampleParentNode',
                        ],
                    'rows' => 1,
                    'page' => 1,
                ]
            );

        $resultSearchQuery = $this->subject->createSearchQuery($request);
        $this->assertEquals($resultSearchQuery->getPage(), 1);
        $this->assertEquals($resultSearchQuery->getParentClass(), 'exampleParentNode');
        $this->assertEquals($resultSearchQuery->getRootClass(), 'exampleRootNode');
        $this->assertEquals($resultSearchQuery->getRows(), 1);
        $this->assertEquals($resultSearchQuery->getTerm(), 'exampleQuery');
        $this->assertEquals($resultSearchQuery->getStartRow(), 0);
    }

    public function testCreateSearchQueryWithPoorRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getQueryParams')
            ->willReturn(
                [
                    'params' =>
                        [
                            'query' => 'exampleQuery',
                            'parentNode' => 'exampleParentNode',
                        ],
                    'rows' => 1,
                    'page' => 1,
                ]
            );

        $this->expectException(CreateSearchQueryException::class);
        $this->subject->createSearchQuery($request);
    }
    public function testCreateSearchQueryRequestWithoutPagination(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getQueryParams')
            ->willReturn(
                [
                    'params' =>
                        [
                            'query' => 'exampleQuery',
                            'parentNode' => 'exampleParentNode',
                            'rootNode' => 'exampleRootNode',
                        ],
                ]
            );

        $resultSearchQuery = $this->subject->createSearchQuery($request);
        $this->assertNull($resultSearchQuery->getRows());
        $this->assertNull($resultSearchQuery->getPage());
    }
}
