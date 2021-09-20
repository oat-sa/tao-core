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

use common_persistence_sql_Platform;
use common_persistence_SqlPersistence;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use Doctrine\DBAL\Query\QueryBuilder;
use oat\generis\persistence\PersistenceManager;
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Domain\Dependency;
use oat\tao\model\Lists\Business\Domain\DependencyCollection;
use oat\tao\model\Lists\DataAccess\Repository\DependencyRepository;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;
use oat\tao\model\Lists\Business\Domain\Value;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\kernel\persistence\smoothsql\search\GateWay;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\QueryInterface;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\DataAccess\Repository\ParentPropertyListRepository;

class ParentPropertyListRepositoryTest extends TestCase
{
    /** @var ParentPropertyListRepository|MockObject */
    private $subject;

    /** @var DependsOnPropertyRepository|MockObject */
    private $sut;

    /** @var core_kernel_classes_Resource|MockObject */
    private $range;

    /** @var QueryBuilder|MockObject */
    private $queryBuilder;

    /** @var RdsValueCollectionRepository|MockObject */
    private $rdsCollectionRepository;

    /** @var Value|MockObject */
    private $valueMock;

    /** @var valueCollection|MockObject */
    private $valueCollection;

    /** @var ComplexSearchService|MockObject */
    private $complexSearchService;    

    public function setUp(): void
    {
        $this->subject = $this->createMock(ParentPropertyListRepository::class);
        $this->sut = $this->createMock(DependencyRepository::class);
        $this->range = $this->createMock(core_kernel_classes_Resource::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->rdsCollectionRepository = $this->createMock(RdsValueCollectionRepository::class);
        $this->valueMock = $this->createMock(Value::class);
        $this->valueCollection = $this->createMock(ValueCollection::class);

        $platform = $this->createMock(common_persistence_sql_Platform::class);
        $platform->method('getQueryBuilder')
            ->willReturn($this->queryBuilder);

        $persistence = $this->createMock(common_persistence_SqlPersistence::class);
        $persistence->method('getPlatform')
            ->willReturn($platform);

        $persistenceManager = $this->createMock(PersistenceManager::class);
        $persistenceManager->method('getPersistenceById')
            ->willReturn($persistence);
            $this->sut->setServiceLocator(
                $this->getServiceLocatorMock(
                    [
                        PersistenceManager::SERVICE_ID => $persistenceManager
                    ]
                )
            );

        $this->complexSearchService = $this->createMock(ComplexSearchService::class);
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    ComplexSearchService::SERVICE_ID => $this->complexSearchService
                ]
            )
        );
    }

    public function testFindAllUris(): void
    {
        $collection = new DependencyCollection();
        $collection->append(new Dependency('uri'));

        $this->range->method('getUri')
            ->willReturn('list_uri');

        $this->sut
            ->method('findAll')
            ->willReturn(
                $collection
            );

        $this->valueCollection
            ->method('getListUris')
            ->willReturn([]);

        $sampleValue = new Value(null, 'http://sample.com#1', '1');
        $this->valueMock
            ->expects(self::any())
            ->method('setUri')
            ->with(['uri'])
            ->willReturn($sampleValue);

        $this->rdsCollectionRepository
            ->method('findAll')
            ->with(new ValueCollectionSearchRequest())
            ->willReturn($this->valueCollection);

        $queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $criteria = $this->createMock(QueryInterface::class);
        $gateway = $this->createMock(GateWay::class);

        $this->complexSearchService
            ->method('query')
            ->willReturn($queryBuilder);

        $this->complexSearchService
            ->method('searchType')
            ->with($queryBuilder, 'uri', true)
            ->willReturn($criteria);

        $this->complexSearchService
            ->method('getGateway')
            ->willReturn($gateway);

        $queryBuilder->method('setCriteria')
            ->willReturnSelf();

        $gateway->method('search')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));
        
        $result = $this->subject->findAllUris(['list_uri' => "uri1"]);

        $this->assertIsArray($result);
    }
}
