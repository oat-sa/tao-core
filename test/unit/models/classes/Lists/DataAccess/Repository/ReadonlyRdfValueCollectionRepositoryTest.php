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

use common_persistence_sql_Platform as SqlPlatform;
use common_persistence_SqlPersistence as SqlPersistence;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Exception;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\generis\persistence\PersistenceManager;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Service\RemoteSourcedListOntology;
use oat\tao\model\Lists\DataAccess\Repository\RdfValueCollectionRepository;
use oat\tao\model\Lists\DataAccess\Repository\ReadonlyRdfValueCollectionProxyRepository;
use oat\tao\model\Lists\DataAccess\Repository\ValueConflictException;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\MockObject\MockObject as PhpUnitMockObject;

class ReadonlyRdfValueCollectionRepositoryTest extends TestCase
{
    private const COLLECTION_URI = 'http://example.com';
    private ReadonlyRdfValueCollectionProxyRepository $sut;

    /** @var RdfValueCollectionRepository|MockObject */
    private $rdfValueCollectionRepositoryMock;

    public function setUp(): void
    {
        $this->rdfValueCollectionRepositoryMock = $this->createMock(RdfValueCollectionRepository::class);

        $this->sut = new ReadonlyRdfValueCollectionProxyRepository($this->rdfValueCollectionRepositoryMock);
    }

    /**
     * @param ValueCollectionSearchRequest $searchRequest
     *
     * @dataProvider findAllDataProvider
     */
    public function testFindAll(ValueCollectionSearchRequest $searchRequest): void
    {
        $result = new ValueCollection(self::COLLECTION_URI, new Value(1, '1', 'Element 1'), new Value(2, '2', 'Element 2'));
        $this->rdfValueCollectionRepositoryMock->method('findAll')
            ->expects(self::once())
            ->willReturn($result);

        $this->assertEquals(
            $result,
            $this->sut->findAll($searchRequest)
        );
    }

    public function testPersistThrowLogicException(): void
    {
        $this->expectException(\LogicException::class);

        $valueCollection = $this->createMock(ValueCollection::class);
        $this->rdfValueCollectionRepositoryMock->method('persist')
            ->expects(self::never());
        $this->expectException(\LogicException::class);

        $this->sut->persist($valueCollection);
    }

    public function testDeleteThrowLogicException(): void
    {
        $this->expectException(\LogicException::class);

        $valueCollection = $this->createMock(ValueCollection::class);
        $this->rdfValueCollectionRepositoryMock->method('delete')
            ->expects(self::never());
        $this->expectException(\LogicException::class);

        $this->sut->delete($valueCollection);
    }

    public function testIsApplicable(): void
    {
        $this->assertTrue($this->sut->isApplicable(TaoOntology::CLASS_URI_READONLY_LIST));
        $this->assertFalse($this->sut->isApplicable(''));
        $this->assertFalse($this->sut->isApplicable(RemoteSourcedListOntology::LIST_TYPE_REMOTE));
    }

    /**
     * @dataProvider countDataProvider
     */
    public function testCount(
        int $expected,
        $fetchResult,
        ?string $valueCollectionUri,
        array $queryParams
    ): void {
        $hasValueCollectionUri =!empty($valueCollectionUri);

        $searchRequest = $this->createMock(ValueCollectionSearchRequest::class);
        $searchRequest
            ->expects($this->atLeastOnce())
            ->method('hasValueCollectionUri')
            ->willReturn($hasValueCollectionUri);

        $searchRequest
            ->expects($hasValueCollectionUri ? $this->atLeastOnce() : $this->never())
            ->method('getValueCollectionUri')
            ->willReturn($valueCollectionUri);

        $this->rdfValueCollectionRepositoryMock
            ->expects($this->once())
            ->method('count')
            ->with($queryParams)
            ->willReturn($fetchResult);
        $this->assertEquals($expected, $this->sut->count($searchRequest));
    }

    public function countDataProvider(): array
    {
        return [
            'count() with value collection uses its URI for querying' => [
                'expected' => 1,
                'fetchResult' => [1],
                'valueCollectionUri' => 'http://value.collection/',
                'queryParams' => [
                    'label_uri' => 'http://www.w3.org/2000/01/rdf-schema#label',
                    'type_uri' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                    'collection_uri' => 'http://value.collection/',
                ],
            ],
            'count() without value collection does not use its URI for querying' => [
                'expected' => 1,
                'fetchResult' => [1],
                'valueCollectionUri' => '',
                'queryParams' => [
                    'label_uri' => 'http://www.w3.org/2000/01/rdf-schema#label',
                    'type_uri' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                ]
            ],
            'An empty result set is handled gracefully' => [
                'expected' => 0,
                'fetchResult' => [],
                'valueCollectionUri' => 'http://value.collection/',
                'queryParams' => [
                    'label_uri' => 'http://www.w3.org/2000/01/rdf-schema#label',
                    'type_uri' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                    'collection_uri' => 'http://value.collection/',
                ],
            ],
            'A null result set is handled gracefully' => [
                'expected' => 0,
                'fetchResult' => null,
                'valueCollectionUri' => 'http://value.collection/',
                'queryParams' => [
                    'label_uri' => 'http://www.w3.org/2000/01/rdf-schema#label',
                    'type_uri' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                    'collection_uri' => 'http://value.collection/',
                ],
            ],
        ];
    }

    public function findAllDataProvider(): array
    {
        return [
            'Bare search request' => [
                (new ValueCollectionSearchRequest())->setDataLanguage('en'),
            ],
            'Search request with property URI' => [
                (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->setDataLanguage('en'),
            ],
            'Search request with value collection URI' => [
                (new ValueCollectionSearchRequest())
                    ->setValueCollectionUri(self::COLLECTION_URI)
                    ->setDataLanguage('en'),
            ],
            'Search request with subject' => [
                (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->setSubject('test')
                    ->setDataLanguage('en'),
            ],
            'Search request with excluded value URIs' => [
                (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->addExcluded('https://example.com#1')
                    ->addExcluded('https://example.com#2')
                    ->setDataLanguage('en'),
            ],
            'Search request with limit' => [
                (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->setLimit(1)
                    ->setDataLanguage('en'),
            ],
            'Search request with all properties' => [
                (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->setValueCollectionUri(self::COLLECTION_URI)
                    ->setSubject('test')
                    ->addExcluded('https://example.com#1')
                    ->addExcluded('https://example.com#2')
                    ->setLimit(1)
                    ->setDataLanguage('en'),
            ],
        ];
    }

}
