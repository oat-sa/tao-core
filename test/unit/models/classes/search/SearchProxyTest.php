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

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\data\permission\PermissionHelper;
use oat\generis\test\TestCase;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\search\ElasticSearchBridge;
use oat\tao\model\search\GenerisSearchBridge;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\SearchProxy;
use oat\tao\model\search\SearchQueryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;

class SearchProxyTest extends TestCase
{
    /** @var SearchProxy */
    private $subject;

    /** @var PermissionHelper|MockObject */
    private $permissionHelperMock;

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

    /** @var Ontology|MockObject */
    private $modelMock;

    public function setUp(): void
    {
        $this->permissionHelperMock = $this->createMock(PermissionHelper::class);
        $this->advancedSearchCheckerMock = $this->createMock(AdvancedSearchChecker::class);
        $this->elasticSearchBridgeMock = $this->createMock(ElasticSearchBridge::class);
        $this->generisSearchBridgeMock = $this->createMock(GenerisSearchBridge::class);
        $this->searchQueryFactoryMock = $this->createMock(SearchQueryFactory::class);

        $this->resultSetMock = $this->createMock(ResultSet::class);
        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->modelMock = $this->createMock(Ontology::class);

        $this->subject = new SearchProxy();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    PermissionHelper::class => $this->permissionHelperMock,
                    AdvancedSearchChecker::class => $this->advancedSearchCheckerMock,
                    ElasticSearchBridge::class => $this->elasticSearchBridgeMock,
                    GenerisSearchBridge::class => $this->generisSearchBridgeMock,
                    SearchQueryFactory::class => $this->searchQueryFactoryMock,
                ]
            )
        );

        $this->subject->setModel($this->modelMock);

        $this->resultSetMock
            ->method('getArrayCopy')
            ->willReturn(
                [
                    'uri1',
                    'uri2',
                ]
            );

        $this->permissionHelperMock
            ->expects($this->once())
            ->method('filterByPermission')
            ->willReturn(
                [
                    'uri1',
                ]
            );

        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);

        $resourceMock
            ->method('getUri')
            ->willReturn('uri1');

        $resourceMock
            ->method('getLabel')
            ->willReturn('label');

        $this->modelMock
            ->method('getResource')
            ->willReturn($resourceMock);
    }

    public function testSearchWithoutElasticSearch(): void
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
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->willReturn(false);


        $this->generisSearchBridgeMock
            ->method('search')
            ->willReturn($this->resultSetMock);

        $this->resultSetMock
            ->expects($this->exactly(2))
            ->method('getTotalCount')
            ->willReturn(100);

        $result = $this->subject->search($this->requestMock);
        $this->assertResult($result);
        $this->assertEquals(10.0, $result['total']);
    }

    public function testSearchWithNoPagination(): void
    {
        $this->requestMock
            ->method('getQueryParams')
            ->willReturn([]);

        $this->advancedSearchCheckerMock
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->willReturn(true);

        $this->elasticSearchBridgeMock
            ->method('search')
            ->willReturn($this->resultSetMock);

        $this->resultSetMock
            ->expects($this->exactly(1))
            ->method('getTotalCount')
            ->willReturn(100);

        $result = $this->subject->search($this->requestMock);
        $this->assertResult($result);
        $this->assertEquals(1, $result['total']);
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
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->willReturn(true);

        $this->elasticSearchBridgeMock
            ->method('search')
            ->willReturn($this->resultSetMock);

        $this->resultSetMock
            ->expects($this->exactly(2))
            ->method('getTotalCount')
            ->willReturn(100);

        $result = $this->subject->search($this->requestMock);
        $this->assertResult($result);
        $this->assertEquals(10.0, $result['total']);
    }

    private function assertResult(array $result): void
    {
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('readonly', $result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('totalCount', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertEquals(1, $result['page']);
        $this->assertEquals(100, $result['totalCount']);
        $this->assertEquals(2, $result['records']);
        $this->assertCount(2, $result['data']);
        $this->assertCount(1, $result['readonly']);
        $this->assertEquals(
            [
                [
                    'id' => 'uri1',
                    'http://www.w3.org/2000/01/rdf-schema#label' => 'label',
                ],
                [
                    'id' => 'uri1',
                    'http://www.w3.org/2000/01/rdf-schema#label' => 'label',
                ],
            ],
            $result['data']
        );
        $this->assertTrue($result['readonly']['uri2']);
    }
}
