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

use core_kernel_classes_Class;
use core_kernel_classes_Container;
use core_kernel_classes_Literal;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use Exception;
use oat\generis\model\data\Ontology;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\QueryBuilder;
use oat\search\base\QueryInterface;
use oat\search\base\SearchGateWayInterface;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Entity\ResourceTranslatable;
use oat\tao\model\Translation\Entity\ResourceTranslation;
use oat\tao\model\Translation\Factory\ResourceTranslationFactory;
use oat\tao\model\Translation\Query\ResourceTranslatableQuery;
use oat\tao\model\Translation\Query\ResourceTranslationQuery;
use oat\tao\model\Translation\Repository\ResourceTranslatableRepository;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ResourceTranslationRepositoryTest extends TestCase
{
    private ResourceTranslationRepository $sut;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var ComplexSearchService|MockObject */
    private $complexSearch;

    /** @var ResourceTranslatableRepository|MockObject */
    private $resourceTranslatableRepository;

    /** @var ResourceTranslationFactory|MockObject */
    private $factory;

    /** @var LoggerInterface|MockObject */
    private $logger;

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
        $this->resourceTranslatableRepository = $this->createMock(ResourceTranslatableRepository::class);
        $this->factory = $this->createMock(ResourceTranslationFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->searchQuery = $this->createMock(QueryInterface::class);
        $this->gateway = $this->createMock(SearchGateWayInterface::class);
        $this->resourceTypeClass = $this->createMock(core_kernel_classes_Class::class);

        $this->sut = new ResourceTranslationRepository(
            $this->ontology,
            $this->complexSearch,
            $this->resourceTranslatableRepository,
            $this->factory,
            $this->logger
        );
    }

    public function testFindReturnsResourceCollection(): void
    {
        $resourceType = 'http://www.tao.lu/Ontologies/TAO.rdf#AssessmentContentObject';
        $uniqueIds = ['id1'];
        $translatable1 = $this->createMock(ResourceTranslatable::class);
        $translationResource1 = $this->createMock(core_kernel_classes_Resource::class);
        $translation1 = $this->createMock(ResourceTranslation::class);
        $uniqueIdProperty = $this->createMock(core_kernel_classes_Property::class);
        $uniqueId = new core_kernel_classes_Literal('id1');

        $translatable1
            ->method('getUniqueId')
            ->willReturn('id1');

        $query = $this->createMock(ResourceTranslationQuery::class);
        $query
            ->method('getResourceType')
            ->willReturn($resourceType);
        $query
            ->method('getResourceUri')
            ->willReturn($uniqueIds);

        $this->resourceTranslatableRepository
            ->method('find')
            ->willReturn(new ResourceCollection($translatable1));

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
                    $translationResource1
                ]
            );

        $this->ontology
            ->method('getClass')
            ->with($resourceType)
            ->willReturn($this->resourceTypeClass);

        $this->ontology
            ->method('getProperty')
            ->with(TaoOntology::PROPERTY_UNIQUE_IDENTIFIER)
            ->willReturn($uniqueIdProperty);

        $translationResource1
            ->method('isInstanceOf')
            ->with($this->resourceTypeClass)
            ->willReturn(true);

        $translationResource1
            ->method('getOnePropertyValue')
            ->with($uniqueIdProperty)
            ->willReturn($uniqueId);

        $this->factory
            ->method('create')
            ->willReturnMap(
                [
                    [$translatable1, $translationResource1, $translation1]
                ]
            );

        $result = $this->sut->find($query);

        $this->assertInstanceOf(ResourceCollection::class, $result);
        $this->assertCount(1, $result);
        $this->assertContains($translation1, $result);
    }
}
