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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\DataAccess\Repository;

use ArrayIterator;
use oat\generis\test\TestCase;
use core_kernel_classes_Property;
use oat\generis\model\OntologyRdf;
use oat\search\base\QueryInterface;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\SearchGateWayInterface;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\Lists\DataAccess\Repository\DependentPropertiesRepository;
use oat\tao\model\Lists\Business\Domain\DependentPropertiesRepositoryContext;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;

class DependentPropertiesRepositoryTest extends TestCase
{
    /** @var DependentPropertiesRepository */
    private $sut;

    /** @var ComplexSearchService|MockObject */
    private $complexSearchService;

    protected function setUp(): void
    {
        $this->complexSearchService = $this->createMock(ComplexSearchService::class);

        $this->sut = new DependentPropertiesRepository();
        $this->sut->setServiceLocator(
            $this->getServiceLocatorMock([
                ComplexSearchService::SERVICE_ID => $this->complexSearchService,
            ])
        );
    }

    public function testFindAll(): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->method('getUri')
            ->willReturn('propertyUri');

        $context = $this->createMock(DependentPropertiesRepositoryContext::class);
        $context
            ->method('getParameter')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));

        $queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $queryBuilder
            ->method('setCriteria')
            ->willReturnSelf();

        $gateway = $this->createMock(SearchGateWayInterface::class);
        $gateway
            ->method('search')
            ->willReturn(new ArrayIterator([$property]));

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

        $this->assertEquals([$property], $this->sut->findAll($context));
    }
}
