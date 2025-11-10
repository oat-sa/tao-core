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
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\DataAccess\Repository;

use common_persistence_SqlPersistence as SqlPersistence;
use oat\generis\model\data\Ontology;
use oat\generis\model\data\RdfsInterface;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\OntologyRdfs;
use oat\generis\persistence\PersistenceManager;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\event\EventAggregator;
use oat\oatbox\event\EventManager;
use oat\oatbox\service\ServiceManager;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\SearchGateWayInterface;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\DataAccess\Repository\RdfValueCollectionRepository;
use oat\tao\model\Lists\DataAccess\Repository\ValueConflictException;
use oat\tao\model\search\ResultSet;
use oat\tao\test\unit\models\classes\Lists\DataAccess\Repository\QueryStub;

class RdfValueCollectionRepositoryTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const PERSISTENCE_ID = 'test';

    private const COLLECTION_URI = 'http://example.com';

    /** @var RdfValueCollectionRepository|MockObject */
    private $sut;

    private QueryStub $criterion;

    /**
     * @var SearchGateWayInterface|MockObject
     */
    private $gateway;

    /**
     * @var \core_kernel_persistence_ResourceInterface|MockObject
     */
    private $resourceMock;

    protected function setUp(): void
    {
        $persistenceManagerMock = $this->mockPersistance();
        $complexSearchService = $this->mockComplexSearch();
        $ontology = $this->mockOntology($complexSearchService);
        $serviceManager = $this->mockServiceLocator($ontology);

        $this->sut = $this->getMockBuilder(RdfValueCollectionRepository::class)
            ->onlyMethods(['verifyUriUniqueness'])
            ->setConstructorArgs([$persistenceManagerMock, self::PERSISTENCE_ID])
            ->getMock();
        $this->sut->setServiceLocator($serviceManager);
    }

    /**
     * @dataProvider findAllDataProvider
     */
    public function testFindAll(ValueCollectionSearchRequest $searchRequest, array $queryParams): void
    {
        $expectedResult = new ValueCollection(
            $searchRequest->hasValueCollectionUri() ? $searchRequest->getValueCollectionUri() : null,
            new Value(1, '1', 'Element 1'),
            new Value(2, '2', 'Element 2')
        );

        if ($searchRequest->hasPropertyUri()) {
            $this->resourceMock
                ->method('getPropertyValues')
                ->with(
                    new \core_kernel_classes_Property($searchRequest->getPropertyUri()),
                    new \core_kernel_classes_Property(OntologyRdfs::RDFS_RANGE),
                    []
                )
                ->willReturn(['http://url']);
        }

        $this->prepareResponse($searchRequest, $expectedResult);

        $this->assertEquals($expectedResult, $this->sut->findAll($searchRequest), 'Result is incorrect');
        $this->assertEquals($queryParams, $this->criterion->getCriteriaList(), 'Query params are incorrect');
    }

    public function testPersistDuplicates(): void
    {
        $this->expectException(ValueConflictException::class);

        $valueCollection = $this->createMock(ValueCollection::class);
        $valueCollection->method('hasDuplicates')->willReturn(true);

        $this->sut->persist($valueCollection);
    }

    public function testPersistUpdateNoChanges(): void
    {
        $this->resourceMock
            ->expects(self::never())
            ->method('createInstance');

        $this->resourceMock
            ->expects(self::never())
            ->method('setPropertyValue');

        $this->resourceMock
            ->expects(self::never())
            ->method('updateUri');

        $value = new Value(666, 'uri', 'label');

        $valueCollection = new ValueCollection('http://url', $value);

        $this->assertTrue($this->sut->persist($valueCollection));
    }

    public function testPersistUpdate(): void
    {
        $this->resourceMock
            ->expects(self::once())
            ->method('removePropertyValues');

        $this->resourceMock
            ->expects(self::once())
            ->method('setPropertyValue');

        $value = new Value(666, 'uri1', 'label');
        $value->setLabel('newLabel');

        $valueCollection = new ValueCollection('http://url', $value);

        $this->assertTrue($this->sut->persist($valueCollection));
    }

    public function testPersistUpdateDifferentUris(): void
    {
        $this->resourceMock
            ->expects(self::once())
            ->method('updateUri');

        $value = new Value(666, 'uri1', 'label');
        $value->setUri('uri2');

        $valueCollection = new ValueCollection('http://url', $value);

        $this->assertTrue($this->sut->persist($valueCollection));
    }

    public function testPersistInsert(): void
    {
        $this->resourceMock
            ->expects(self::once())
            ->method('createInstance')
            ->willReturn(new \core_kernel_classes_Resource('uri1'));

        $value = new Value(null, 'uri1', 'label');

        $valueCollection = new ValueCollection('http://url', $value);

        $this->assertTrue($this->sut->persist($valueCollection));
    }

    /**
     * @dataProvider countDataProvider
     */
    public function testCount(
        int $expected,
        ?string $valueCollectionUri,
        array $queryParams
    ): void {

        $searchRequest = new ValueCollectionSearchRequest();
        $searchRequest->setValueCollectionUri($valueCollectionUri);

        $this->gateway->expects(self::once())
            ->method('count')
            ->willReturn($expected);

        $this->assertEquals($expected, $this->sut->count($searchRequest));
        $this->assertEquals($queryParams, $this->criterion->getCriteriaList());
    }

    public function countDataProvider(): array
    {
        return [
            'count() with value collection uses its URI for querying' => [
                'expected' => 1,
                'valueCollectionUri' => 'http://value.collection/',
                'queryParams' => [
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type is in [http://value.collection/]',
                ],
            ],
            'count() without value collection does not use its URI for querying' => [
                'expected' => 1,
                'valueCollectionUri' => '',
                'queryParams' => []
            ],
            'An empty result set is handled gracefully' => [
                'expected' => 0,
                'valueCollectionUri' => 'http://value.collection/',
                'queryParams' => [
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type is in [http://value.collection/]',
                ],
            ],
            'A null result set is handled gracefully' => [
                'expected' => 0,
                'valueCollectionUri' => 'http://value.collection/',
                'queryParams' => [
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type is in [http://value.collection/]',
                ],
            ],
        ];
    }

    public function findAllDataProvider(): array
    {
        return [
            'Bare search request' => [
                'searchRequest' => (new ValueCollectionSearchRequest())->setDataLanguage('en'),
                'queryParams' => [],
            ],
            'Search request with property URI' => [
                'searchRequest' => (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->setDataLanguage('en'),
                'queryParams' => [
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type is in [http://url]',
                ],
            ],
            'Search request with value collection URI' => [
                'searchRequest' => (new ValueCollectionSearchRequest())
                    ->setValueCollectionUri(self::COLLECTION_URI)
                    ->setDataLanguage('en'),
                'queryParams' => [
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type is in [http://example.com]',
                ],
            ],
            'Search request with subject' => [
                'searchRequest' => (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->setSubject('test')
                    ->setDataLanguage('en'),
                'queryParams' => [
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type is in [http://url]',
                    'http://www.w3.org/2000/01/rdf-schema#label is contains [test]',
                ],
            ],
            'Search request with excluded value URIs' => [
                'searchRequest' => (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->addExcluded('https://example.com#1')
                    ->addExcluded('https://example.com#2')
                    ->setDataLanguage('en'),
                'queryParams' => [
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type is in [http://url]',
                    'uri is notIn [https://example.com#1,https://example.com#2]',
                ],
            ],
            'Search request with limit' => [
                'searchRequest' => (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->setLimit(1)
                    ->setDataLanguage('en'),
                'queryParams' => [
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type is in [http://url]'
                ],
            ],
            'Search request with all properties' => [
                'searchRequest' => (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->setValueCollectionUri(self::COLLECTION_URI)
                    ->setSubject('test')
                    ->addExcluded('https://example.com#1')
                    ->addExcluded('https://example.com#2')
                    ->setLimit(1)
                    ->setDataLanguage('en'),
                'queryParams' => [
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type is in [http://url,http://example.com]',
                    'http://www.w3.org/2000/01/rdf-schema#label is contains [test]',
                    'uri is notIn [https://example.com#1,https://example.com#2]',
                ],
            ],
        ];
    }

    private function mockPersistance()
    {
        $persistenceManagerMock = $this->createMock(PersistenceManager::class);
        $persistenceMock = $this->createMock(SqlPersistence::class);

        $persistenceManagerMock
            ->method('getPersistenceById')
            ->with(self::PERSISTENCE_ID)
            ->willReturn($persistenceMock);

        $persistenceMock
            ->method('transactional')
            ->willReturnCallback(
                function (\Closure $function) {
                    return $function();
                }
            );

        return $persistenceManagerMock;
    }

    private function prepareResponse(ValueCollectionSearchRequest $searchRequest, ValueCollection $result): void
    {
        $tripleList = [];
        foreach ($result as $expectedValue) {
            $triple = new \core_kernel_classes_Triple();

            $triple->id = $expectedValue->getId();
            $triple->subject = $expectedValue->getUri();
            $triple->object = $expectedValue->getLabel();

            $tripleList[] = $triple;
        }

        $this->gateway->expects(self::once())
            ->method('searchTriples')
            ->willReturn(new ResultSet($tripleList, count($tripleList)));
    }

    public function mockComplexSearch()
    {
        $this->criterion = new QueryStub();
        $this->gateway = $this->createMock(SearchGateWayInterface::class);

        $queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $queryBuilder->method('setCriteria')->willReturnSelf();
        $queryBuilder->method('newQuery')->willReturn($this->criterion);

        $complexSearchService = $this->createMock(ComplexSearchService::class);
        $complexSearchService->method('query')->willReturn($queryBuilder);
        $complexSearchService->method('getGateway')->willReturn($this->gateway);

        return $complexSearchService;
    }

    /**
     * @param $complexSearchService
     *
     * @return Ontology|MockObject
     */
    public function mockOntology($complexSearchService)
    {
        $this->resourceMock = $this->createMock(
            \core_kernel_persistence_ClassInterface::class
        );

        $mockRdfs = $this->createMock(RdfsInterface::class);
        $mockRdfs->method('getClassImplementation')->willReturn($this->resourceMock);
        $mockRdfs->method('getResourceImplementation')->willReturn($this->resourceMock);
        $mockRdfs->method('getPropertyImplementation')->willReturn($this->resourceMock);

        $ontology = $this->createMock(Ontology::class);
        $ontology->method('getOptions')->willReturn(['persistence' => self::PERSISTENCE_ID]);
        $ontology->method('getRdfsInterface')->willReturn($mockRdfs);
        $ontology->method('getSearchInterface')->willReturn($complexSearchService);
        $ontology->method('getProperty')->willReturnCallback(function ($uri) use ($ontology) {
            $property = new \core_kernel_classes_Property($uri);
            $property->setModel($ontology);
            return $property;
        });
        return $ontology;
    }

    /**
     * @param $ontology
     *
     * @return ServiceManager|MockObject
     */
    public function mockServiceLocator($ontology)
    {
        $eventAggregator = new EventAggregator();
        $eventManager = new EventManager();

        $serviceManager = $this->getServiceManagerMock(
            [
                Ontology::SERVICE_ID => $ontology,
                EventAggregator::SERVICE_ID => $eventAggregator,
                EventManager::SERVICE_ID => $eventManager,
            ]
        );

        ServiceManager::setServiceManager($serviceManager);
        $eventManager->setServiceLocator($serviceManager);

        return $serviceManager;
    }
}
