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
 * Copyright (c) 2021-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Lists\DataAccess\Repository;

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Property;
use oat\generis\model\OntologyRdf;
use oat\search\base\QueryInterface;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\ResultSetInterface;
use oat\search\base\SearchGateWayInterface;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\Lists\DataAccess\Repository\DependentPropertiesRepository;
use oat\tao\model\Lists\Business\Domain\DependentPropertiesRepositoryContext;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;

class DependentPropertiesRepositoryTest extends TestCase
{
    use ServiceManagerMockTrait;

    private DependentPropertiesRepository $sut;
    private ComplexSearchService|MockObject $complexSearchService;

    protected function setUp(): void
    {
        $this->complexSearchService = $this->createMock(ComplexSearchService::class);

        $this->sut = new DependentPropertiesRepository();
        $this->sut->setServiceLocator(
            $this->getServiceManagerMock([
                ComplexSearchService::SERVICE_ID => $this->complexSearchService,
            ])
        );
    }

    public function testFindAll(): void
    {
        $this->mockFindSearch();

        $context = $this->createMock(DependentPropertiesRepositoryContext::class);
        $context
            ->method('getParameter')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));

        $this->assertSame([], $this->sut->findAll($context));
    }

    public function testFindTotalChildren(): void
    {
        $this->mockFindSearch();

        $context = $this->createMock(DependentPropertiesRepositoryContext::class);
        $context
            ->method('getParameter')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));

        $this->assertSame(1, $this->sut->findTotalChildren($context));
    }

    private function mockFindSearch(): void
    {
        $queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $queryBuilder
            ->method('setCriteria')
            ->willReturnSelf();

        $result = $this->createMock(ResultSetInterface::class);
        $result->method('total')
            ->willReturn(1);
        $gateway = $this->createMock(SearchGateWayInterface::class);
        $gateway
            ->method('search')
            ->willReturn($result);

        $this->complexSearchService
            ->method('query')
            ->willReturn($queryBuilder);
        $this->complexSearchService
            ->method('searchType')
            ->with($queryBuilder, OntologyRdf::RDF_PROPERTY, true)
            ->willReturn($this->createMock(QueryInterface::class));
        $this->complexSearchService
            ->method('getGateway')
            ->willReturn($gateway);
    }
}
