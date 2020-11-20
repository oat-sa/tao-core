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

use oat\generis\test\TestCase;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\search\ElasticSearchBridge;
use oat\tao\model\search\GenerisSearchBridge;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\ResultSetResponseNormalizer;
use oat\tao\model\search\SearchProxy;
use oat\tao\model\search\SearchQueryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;

class SearchProxyTest extends TestCase
{
    /** @var SearchProxy */
    private $subject;

    /** @var AdvancedSearchChecker|MockObject */
    private $advancedSearchCheckerMock;

    /** @var ElasticSearchBridge|MockObject */
    private $elasticSearchBridgeMock;

    /** @var GenerisSearchBridge|MockObject */
    private $generisSearchBridgeMock;

    /** @var SearchQueryFactory|MockObject */
    private $searchQueryFactoryMock;

    /** @var ResultSet|MockObject */
    private $resultSetMock;

    /** @var ServerRequestInterface|MockObject */
    private $requestMock;

    /** @var ResultSetResponseNormalizer|MockObject */
    private $resultSetResponseNormalizerMock;

    public function setUp(): void
    {
        $this->advancedSearchCheckerMock = $this->createMock(AdvancedSearchChecker::class);
        $this->elasticSearchBridgeMock = $this->createMock(ElasticSearchBridge::class);
        $this->generisSearchBridgeMock = $this->createMock(GenerisSearchBridge::class);
        $this->searchQueryFactoryMock = $this->createMock(SearchQueryFactory::class);
        $this->resultSetResponseNormalizerMock = $this->createMock(ResultSetResponseNormalizer::class);

        $this->resultSetMock = $this->createMock(ResultSet::class);
        $this->requestMock = $this->createMock(ServerRequestInterface::class);

        $this->subject = new SearchProxy();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    AdvancedSearchChecker::class => $this->advancedSearchCheckerMock,
                    ElasticSearchBridge::class => $this->elasticSearchBridgeMock,
                    GenerisSearchBridge::class => $this->generisSearchBridgeMock,
                    SearchQueryFactory::class => $this->searchQueryFactoryMock,
                    ResultSetResponseNormalizer::class => $this->resultSetResponseNormalizerMock
                ]
            )
        );
    }

    public function testSearchWithoutElasticSearch(): void
    {
        $this->advancedSearchCheckerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->generisSearchBridgeMock
            ->method('search')
            ->willReturn($this->resultSetMock);

        $this->resultSetResponseNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $result = $this->subject->search($this->requestMock);
        $this->assertIsArray($result);
    }

    public function testSearchWithNoPagination(): void
    {
        $this->requestMock
            ->method('getQueryParams')
            ->willReturn([]);

        $this->advancedSearchCheckerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->elasticSearchBridgeMock
            ->method('search')
            ->willReturn($this->resultSetMock);

        $this->resultSetResponseNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $result = $this->subject->search($this->requestMock);
        $this->assertIsArray($result);
    }

    public function testSearchWithElasticSearch(): void
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

        $this->elasticSearchBridgeMock
            ->method('search')
            ->willReturn($this->resultSetMock);

        $this->resultSetResponseNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $result = $this->subject->search($this->requestMock);
        $this->assertIsArray($result);
    }
}
