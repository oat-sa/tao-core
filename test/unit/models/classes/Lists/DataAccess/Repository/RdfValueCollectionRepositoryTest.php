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
use oat\tao\model\Lists\DataAccess\Repository\RdfValueCollectionRepository;
use oat\tao\model\Lists\DataAccess\Repository\ValueConflictException;
use PHPUnit\Framework\MockObject\MockObject as PhpUnitMockObject;

class RdfValueCollectionRepositoryTest extends TestCase
{
    private const PERSISTENCE_ID = 'test';

    private const COLLECTION_URI = 'http://example.com';

    /** @var PersistenceManager|MockObject */
    private $persistenceManagerMock;

    /** @var SqlPersistence|MockObject */
    private $persistenceMock;

    /** @var Connection|MockObject */
    private $connectionMock;

    /** @var MySqlPlatform|MockObject */
    private $platformMock;

    /** @var RdfValueCollectionRepository|MockObject */
    private $sut;

    /** @var string[] */
    private $conditions = [];

    /** @var array */
    private $queryParameters = [];

    /** @var int[] */
    private $queryParameterTypes = [];

    /** @var SqlPlatform|PhpUnitMockObject */
    private $sqlPlatformMock;

    /**
     * @before
     */
    public function init(): void
    {
        $this->platformMock = $this->createMock(MySqlPlatform::class);
        $this->connectionMock = $this->createPartialMock(
            Connection::class,
            [
                'getDatabasePlatform',
                'getExpressionBuilder',
                'executeQuery',
                'connect',
                'beginTransaction',
                'executeUpdate'
            ]
        );

        $this->sqlPlatformMock = $this->getMockBuilder(SqlPlatform::class)
            ->onlyMethods(['rollBack', 'commit'])
            ->setConstructorArgs([$this->connectionMock])
            ->getMock();

        $this->persistenceMock = $this->createMock(SqlPersistence::class);
        $this->persistenceManagerMock = $this->createMock(PersistenceManager::class);

        $this->setUpInitialMockExpectations();

        $this->sut = $this->getMockBuilder(RdfValueCollectionRepository::class)
            ->onlyMethods(['insert', 'verifyUriUniqueness'])
            ->setConstructorArgs([$this->persistenceManagerMock, self::PERSISTENCE_ID])
            ->getMock();
    }

    /**
     * @param ValueCollectionSearchRequest $searchRequest
     *
     * @dataProvider dataProvider
     */
    public function testFindAll(ValueCollectionSearchRequest $searchRequest): void
    {
        $result = new ValueCollection(self::COLLECTION_URI, new Value(1, '1', '1'), new Value(2, '2', '2'));

        $this->expectQuery($searchRequest, $result);

        $this->assertEquals(
            $result,
            $this->sut->findAll($searchRequest)
        );
    }

    public function testPersistDuplicates(): void
    {
        $this->expectException(ValueConflictException::class);

        $valueCollection = $this->createMock(ValueCollection::class);
        $valueCollection->method('hasDuplicates')->willReturn(true);

        $this->sut->persist($valueCollection);
    }

    public function testPersistRollback(): void
    {
        $this->sut
            ->method('insert')
            ->willThrowException(new Exception());
        $this->sqlPlatformMock->expects($this->once())->method('rollback');

        $valueCollection = new ValueCollection('http://url', new Value(null, '', ''));

        $result = $this->sut->persist($valueCollection);

        $this->assertFalse($result);
    }

    public function testPersistUpdateNoChanges(): void
    {
        $this->connectionMock
            ->expects(static::never())
            ->method('executeUpdate');

        $this->connectionMock
            ->expects(static::never())
            ->method('executeQuery');

        $value = new Value(666, 'uri', 'label');

        $valueCollection = new ValueCollection('http://url', $value);

        $result = $this->sut->persist($valueCollection);

        $this->assertTrue($result);
    }

    public function testPersistUpdate(): void
    {
        $this->connectionMock
            ->expects(static::once())
            ->method('executeUpdate');

        $value = new Value(666, 'uri1', 'label');
        $value->setLabel('newLabel');

        $valueCollection = new ValueCollection('http://url', $value);

        $result = $this->sut->persist($valueCollection);

        $this->assertTrue($result);
    }

    public function testPersistUpdateDifferentUris(): void
    {
        $this->connectionMock
            ->expects(static::exactly(3))
            ->method('executeUpdate');

        $value = new Value(666, 'uri1', 'label');
        $value->setUri('uri2');

        $valueCollection = new ValueCollection('http://url', $value);

        $result = $this->sut->persist($valueCollection);

        $this->assertTrue($result);
    }

    public function testPersistInsert(): void
    {
        $this->sut->expects($this->once())->method('insert');

        $value = new Value(null, 'uri1', 'label');

        $valueCollection = new ValueCollection('http://url', $value);

        $result = $this->sut->persist($valueCollection);

        $this->assertTrue($result);
    }

    public function dataProvider(): array
    {
        return [
            'Bare search request'                      => [
                new ValueCollectionSearchRequest(),
            ],
            'Search request with property URI'         => [
                (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com'),
            ],
            'Search request with value collection URI' => [
                (new ValueCollectionSearchRequest())
                    ->setValueCollectionUri(self::COLLECTION_URI),
            ],
            'Search request with subject'              => [
                (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->setSubject('test'),
            ],
            'Search request with excluded value URIs'  => [
                (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->addExcluded('https://example.com#1')
                    ->addExcluded('https://example.com#2'),
            ],
            'Search request with limit'                => [
                (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->setLimit(1),
            ],
            'Search request with all properties'       => [
                (new ValueCollectionSearchRequest())
                    ->setPropertyUri('https://example.com')
                    ->setValueCollectionUri(self::COLLECTION_URI)
                    ->setSubject('test')
                    ->addExcluded('https://example.com#1')
                    ->addExcluded('https://example.com#2')
                    ->setLimit(1),
            ],
        ];
    }

    private function setUpInitialMockExpectations(): void
    {
        $this->persistenceManagerMock
            ->method('getPersistenceById')
            ->with(self::PERSISTENCE_ID)
            ->willReturn($this->persistenceMock);

        $this->persistenceMock
            ->method('getPlatform')
            ->willReturn($this->sqlPlatformMock);

        $this->connectionMock
            ->method('getDatabasePlatform')
            ->willReturn($this->platformMock);

        $this->connectionMock
            ->method('getExpressionBuilder')
            ->willReturn(new ExpressionBuilder($this->connectionMock));
    }

    private function createQuery(ValueCollectionSearchRequest $searchRequest): string
    {
        $queryParts = [
            $this->createInitialQuery(),
            $this->createPropertyUriCondition($searchRequest),
            $this->createValueCollectionUriCondition($searchRequest),
            $this->createSubjectCondition($searchRequest),
            $this->createExcludedCondition($searchRequest),
            $this->createCondition(),
            $this->createLimit($searchRequest),
        ];

        return implode(' ', array_filter($queryParts));
    }

    private function createInitialQuery(): string
    {
        $this->queryParameters = [
            'label_uri' => OntologyRdfs::RDFS_LABEL,
            'type_uri'  => OntologyRdf::RDF_TYPE,
        ];

        $this->conditions = [
            '(element.predicate = :label_uri)',
            'AND (collection.predicate = :type_uri)',
        ];

        return implode(
            ' ',
            [
                'SELECT collection.object as collection_uri, element.id, element.subject, element.object',
                'FROM statements element',
                'INNER JOIN statements collection',
                'ON collection.subject = element.subject',
            ]
        );
    }

    private function createPropertyUriCondition(ValueCollectionSearchRequest $searchRequest): ?string
    {
        if (!$searchRequest->hasPropertyUri()) {
            return null;
        }

        $this->queryParameters['property_uri'] = $searchRequest->getPropertyUri();
        $this->queryParameters['range_uri'] = OntologyRdfs::RDFS_RANGE;

        $this->conditions[] = 'AND (property.subject = :property_uri)';
        $this->conditions[] = 'AND (property.predicate = :range_uri)';

        return implode(
            ' ',
            [
                'INNER JOIN statements property',
                'ON property.object = collection.object',
            ]
        );
    }

    private function createValueCollectionUriCondition(ValueCollectionSearchRequest $searchRequest): ?string
    {
        if (!$searchRequest->hasValueCollectionUri()) {
            return null;
        }

        $this->queryParameters['collection_uri'] = $searchRequest->getValueCollectionUri();

        $this->conditions[] = 'AND (collection.object = :collection_uri)';

        return null;
    }

    private function createSubjectCondition(ValueCollectionSearchRequest $searchRequest): ?string
    {
        if (!$searchRequest->hasSubject()) {
            return null;
        }

        $this->queryParameters['subject'] = "{$searchRequest->getSubject()}%";

        $this->conditions[] = 'AND (element.object LIKE :subject)';

        return null;
    }

    private function createExcludedCondition(ValueCollectionSearchRequest $searchRequest): ?string
    {
        if (!$searchRequest->hasExcluded()) {
            return null;
        }

        $this->queryParameters['excluded_value_uri'] = $searchRequest->getExcluded();
        $this->queryParameterTypes['excluded_value_uri'] = Connection::PARAM_STR_ARRAY;

        $this->conditions[] = 'AND (element.subject NOT IN (:excluded_value_uri))';

        return null;
    }

    private function createCondition(): string
    {
        $conditionStatement = implode(' ', $this->conditions);

        return "WHERE $conditionStatement";
    }

    private function createLimit(ValueCollectionSearchRequest $searchRequest): ?string
    {
        if (!$searchRequest->hasLimit()) {
            return null;
        }

        return "LIMIT {$searchRequest->getLimit()}";
    }

    private function expectQuery(ValueCollectionSearchRequest $searchRequest, ValueCollection $result): void
    {
        $statementMock = $this->createMock(ResultStatement::class);

        $statementMock
            ->expects(static::once())
            ->method('fetchAll')
            ->willReturn(
                $this->domainToRawData($result)
            );

        $this->connectionMock
            ->expects(static::once())
            ->method('executeQuery')
            ->with(
                $this->createQuery($searchRequest),
                $this->queryParameters,
                $this->queryParameterTypes,
                null
            )
            ->willReturn($statementMock);
    }

    private function domainToRawData(ValueCollection $valueCollection): array
    {
        $result = [];

        foreach ($valueCollection as $value) {
            $result[] = [
                'collection_uri' => $valueCollection->getUri(),
                'id'             => (string)$value->getId(),
                'subject'        => $value->getUri(),
                'object'         => $value->getLabel(),
            ];
        }

        return $result;
    }
}
