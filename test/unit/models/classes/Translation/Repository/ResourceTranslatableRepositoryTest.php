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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Translation\Repository;

use core_kernel_classes_Resource;
use core_kernel_classes_Class;
use oat\generis\model\data\Ontology;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\QueryBuilder;
use oat\search\base\QueryInterface;
use oat\search\base\SearchGateWayInterface;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Entity\ResourceTranslatable;
use oat\tao\model\Translation\Factory\ResourceTranslatableFactory;
use oat\tao\model\Translation\Query\ResourceTranslatableQuery;
use oat\tao\model\Translation\Repository\ResourceTranslatableRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResourceTranslatableRepositoryTest extends TestCase
{
    private ResourceTranslatableRepository $sut;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var ComplexSearchService|MockObject */
    private $complexSearch;

    /** @var ResourceTranslatableFactory|MockObject */
    private $factory;

    /** @var ResourceTranslatableQuery|MockObject */
    private $query;

    /** @var QueryBuilder|MockObject */
    private $queryBuilder;

    /** @var QueryInterface|MockObject */
    private $searchQuery;

    /** @var SearchGateWayInterface|MockObject */
    private $gateway;

    /** @var core_kernel_classes_Class|MockObject */
    private $resourceTypeClass;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->complexSearch = $this->createMock(ComplexSearchService::class);
        $this->factory = $this->createMock(ResourceTranslatableFactory::class);
        $this->query = $this->createMock(ResourceTranslatableQuery::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->searchQuery = $this->createMock(QueryInterface::class);
        $this->gateway = $this->createMock(SearchGateWayInterface::class);
        $this->resourceTypeClass = $this->createMock(core_kernel_classes_Class::class);

        $this->sut = new ResourceTranslatableRepository(
            $this->ontology,
            $this->complexSearch,
            $this->factory
        );
    }

    public function testFindReturnsResourceCollection(): void
    {
        $resourceType = 'http://www.tao.lu/Ontologies/TAO.rdf#AssessmentContentObject';
        $uniqueIds = [
            'id1',
            'id2'
        ];
        $resource1 = $this->createMock(core_kernel_classes_Resource::class);
        $resource2 = $this->createMock(core_kernel_classes_Resource::class);
        $translatable1 = $this->createMock(ResourceTranslatable::class);
        $translatable2 = $this->createMock(ResourceTranslatable::class);

        $this->query
            ->method('getResourceType')
            ->willReturn($resourceType);
        $this->query
            ->method('getUniqueIds')
            ->willReturn($uniqueIds);

        $this->complexSearch
            ->method('query')
            ->willReturn($this->queryBuilder);
        $this->complexSearch
            ->method('searchType')
            ->willReturn($this->searchQuery);
        $this->complexSearch
            ->method('getGateway')
            ->willReturn($this->gateway);

        $this->gateway
            ->expects($this->once())
            ->method('search')
            ->with($this->queryBuilder)
            ->willReturn(
                [
                    $resource1,
                    $resource2
                ]
            );

        $this->ontology
            ->method('getClass')
            ->with($resourceType)
            ->willReturn($this->resourceTypeClass);

        $resource1
            ->method('isInstanceOf')
            ->with($this->resourceTypeClass)
            ->willReturn(true);
        $resource2
            ->method('isInstanceOf')
            ->with($this->resourceTypeClass)
            ->willReturn(true);

        $this->factory
            ->method('create')
            ->willReturnMap(
                [
                    [$resource1, $translatable1],
                    [$resource2, $translatable2]
                ]
            );

        $result = $this->sut->find($this->query);

        $this->assertInstanceOf(ResourceCollection::class, $result);
        $this->assertCount(2, $result);
        $this->assertContains($translatable1, $result);
        $this->assertContains($translatable2, $result);
    }
}
