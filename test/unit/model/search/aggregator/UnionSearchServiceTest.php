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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\test\unit\model\search\aggregator;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\search\aggregator\UnionSearchService;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\Search;
use Zend\ServiceManager\ServiceLocatorInterface;

class UnionSearchServiceTest extends TestCase
{
    /** @var UnionSearchService|MockObject */
    private $service;

    /** @var ServiceLocatorInterface|MockObject */
    private $serviceLocatorMock;

    /** @var Search|MockObject */
    private $defaultSearchService;

    public function setUp()
    {
        $this->defaultSearchService = $this->getMock(Search::class);
        $this->defaultSearchService->method('query')->willReturn(new ResultSet(['123'], 1));

        $this->serviceLocatorMock = $this->getServiceLocatorMock([
            Search::SERVICE_ID => $this->defaultSearchService,
        ]);

        $this->service = new UnionSearchService();
        $this->service->setServiceLocator($this->serviceLocatorMock);
    }

    public function testItDoesNotFailWithNoOptionPassed()
    {
        $this->service->setOption(UnionSearchService::OPTION_SERVICES, null);
        $this->service->query('needle', 'type');
    }

    public function testItWontFailWithInvalidSearchServicePassed()
    {
        $this->service->setOption(UnionSearchService::OPTION_SERVICES, [new \stdClass()]);
        $this->service->query('needle', 'type');
    }

    public function testItPollsAllServicesPassed()
    {
        $searchService2 = $this->getMock(Search::class);
        $searchService2->method('query')->willReturn(new ResultSet(['456'], 1));

        $searchService3 = $this->getMock(Search::class);
        $searchService3->method('query')->willReturn(new ResultSet(['789'], 1));

        $this->service->setOption(UnionSearchService::OPTION_SERVICES, [$searchService2, $searchService3]);

        $results = $this->service->query('needle', 'type');
        $results = $results->getArrayCopy();
        $this->assertSame([
            '456',
            '789',
            '123',
        ], $results);
    }

    public function testItExcludeDuplicates()
    {
        $searchService2 = $this->getMock(Search::class);
        $searchService2->method('query')->willReturn(new ResultSet(['456'], 1));

        $searchService3 = $this->getMock(Search::class);
        $searchService3->method('query')->willReturn(new ResultSet(['456'], 1));

        $this->service->setOption(UnionSearchService::OPTION_SERVICES, [$searchService2, $searchService3]);

        $results = $this->service->query('needle', 'type');
        $results = $results->getArrayCopy();
        $this->assertSame([
            '456',
            '123',
        ], $results);
    }
}
