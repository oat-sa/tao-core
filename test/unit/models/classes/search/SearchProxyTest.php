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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\search;

use oat\generis\model\GenerisRdf;
use oat\generis\test\TestCase;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\search\IdentifierSearcher;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\ResultSetResponseNormalizer;
use oat\tao\model\search\SearchInterface;
use oat\tao\model\search\SearchProxy;
use oat\tao\model\search\SearchQuery;
use oat\tao\model\search\SearchQueryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;

class SearchProxyTest extends TestCase
{
    /** @var SearchProxy */
    private $subject;

    /** @var AdvancedSearchChecker|MockObject */
    private $advancedSearchCheckerMock;

    /** @var IdentifierSearcher|MockObject */
    private $identifierSearcher;

    /** @var SearchQueryFactory|MockObject */
    private $searchQueryFactoryMock;

    /** @var ResultSet|MockObject */
    private $resultSetMock;

    /** @var ServerRequestInterface|MockObject */
    private $requestMock;

    /** @var ResultSetResponseNormalizer|MockObject */
    private $resultSetResponseNormalizerMock;

    /** @var SearchInterface|MockObject */
    private $defaultSearch;

    /** @var SearchInterface|MockObject */
    private $advancedSearch;

    public function setUp(): void
    {
        $this->advancedSearchCheckerMock = $this->createMock(AdvancedSearchChecker::class);
        $this->identifierSearcher = $this->createMock(IdentifierSearcher::class);
        $this->defaultSearch = $this->createMock(SearchInterface::class);
        $this->advancedSearch = $this->createMock(SearchInterface::class);
        $this->searchQueryFactoryMock = $this->createMock(SearchQueryFactory::class);
        $this->resultSetResponseNormalizerMock = $this->createMock(ResultSetResponseNormalizer::class);

        $this->resultSetMock = $this->createMock(ResultSet::class);
        $this->requestMock = $this->createMock(ServerRequestInterface::class);

        $this->requestMock->method('getQueryParams')->willReturn(
            [
                'params' =>
                    [
                        'query' => 'test',
                        'structure' => 'exampleRootNode',
                    ],
            ]
        );

        $this->subject = new SearchProxy(
            [
                SearchProxy::OPTION_DEFAULT_SEARCH_CLASS => $this->defaultSearch,
                SearchProxy::OPTION_ADVANCED_SEARCH_CLASS => $this->advancedSearch,
            ]
        );
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    AdvancedSearchChecker::class => $this->advancedSearchCheckerMock,
                    SearchQueryFactory::class => $this->searchQueryFactoryMock,
                    ResultSetResponseNormalizer::class => $this->resultSetResponseNormalizerMock,
                    IdentifierSearcher::class => $this->identifierSearcher
                ]
            )
        );
    }

    public function testSearchByIdentifier(): void
    {
        $this->identifierSearcher
            ->method('search')
            ->willReturn(new ResultSet([], 1));

        $this->resultSetResponseNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->assertSame([], $this->subject->search($this->requestMock));
    }

    public function testSearchByDefaultSearch(): void
    {
        $this->advancedSearchCheckerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->defaultSearch
            ->method('query')
            ->willReturn(new ResultSet([], 1));

        $this->resultSetResponseNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->assertSame([], $this->subject->search($this->requestMock));
    }

    public function testForceGenerisSearch(): void
    {
        $query = new SearchQuery(
            '',
            '',
            GenerisRdf::CLASS_ROLE,
            0,
            10,
            0
        );

        $this->searchQueryFactoryMock
            ->method('create')
            ->willReturn($query);

        $this->defaultSearch
            ->method('query')
            ->willReturn($this->resultSetMock);

        $this->resultSetResponseNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->assertSame([], $this->subject->search($this->requestMock));
    }

    public function testSearchWithAdvancedSearchAndNoPagination(): void
    {
        $this->requestMock
            ->method('getQueryParams')
            ->willReturn([]);

        $this->advancedSearchCheckerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->advancedSearch
            ->method('query')
            ->willReturn($this->resultSetMock);

        $this->resultSetResponseNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->assertSame([], $this->subject->search($this->requestMock));
    }

    public function testSearchWithAdvancedSearchAndPagination(): void
    {
        $this->requestMock
            ->method('getQueryParams')
            ->willReturn(
                [
                    'rows' => 10,
                    'page' => 1,
                ]
            );

        $this->advancedSearchCheckerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->advancedSearch
            ->method('query')
            ->willReturn($this->resultSetMock);

        $this->resultSetResponseNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $result = $this->subject->search($this->requestMock);
        $this->assertIsArray($result);
    }

    public function testIsForcingDefaultSearch(): void
    {
        $options = $this->subject->getOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST, []);
        $generisSearchWhitelist = array_merge(SearchProxy::GENERIS_SEARCH_DEFAULT_WHITELIST, $options);
        $this->assertTrue(in_array(GenerisRdf::CLASS_ROLE, $generisSearchWhitelist));
    }
}
