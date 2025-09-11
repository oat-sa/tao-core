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

use core_kernel_classes_Property;
use InvalidArgumentException;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Business\Domain\Dependency;
use oat\tao\model\Lists\Business\Domain\DependencyCollection;
use oat\tao\model\Lists\DataAccess\Repository\DependencyRepository;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\kernel\persistence\smoothsql\search\GateWay;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\QueryInterface;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\DataAccess\Repository\ParentPropertyListRepository;

class ParentPropertyListRepositoryTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ParentPropertyListRepository|MockObject $subject;
    private DependencyRepository|MockObject $dependencyRepository;
    private RdsValueCollectionRepository|MockObject $rdsCollectionRepository;
    private valueCollection|MockObject $valueCollection;
    private ComplexSearchService|MockObject $complexSearchService;

    protected function setUp(): void
    {
        $this->rdsCollectionRepository = $this->createMock(RdsValueCollectionRepository::class);
        $this->valueCollection = $this->createMock(ValueCollection::class);
        $this->dependencyRepository = $this->createMock(DependencyRepository::class);
        $this->complexSearchService = $this->createMock(ComplexSearchService::class);

        $this->subject = new ParentPropertyListRepository();
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    ComplexSearchService::SERVICE_ID => $this->complexSearchService,
                    DependencyRepository::class => $this->dependencyRepository,
                    RdsValueCollectionRepository::SERVICE_ID => $this->rdsCollectionRepository
                ]
            )
        );
    }

    public function testFindAllUrisWillThrowExceptionWhenRequiredParamIsMissing(): void
    {
        $this->expectExceptionMessage('listUri must be provided as a filter');
        $this->expectException(InvalidArgumentException::class);
        $this->subject->findAllUris([]);
    }

    public function testFindAllUris(): void
    {
        $queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $criteria = $this->createMock(QueryInterface::class);
        $gateway = $this->createMock(GateWay::class);
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property->method('getUri')
            ->willReturn('expectedUri');

        $collection = new DependencyCollection();
        $collection->append(new Dependency('uri'));

        $this->dependencyRepository
            ->method('findAll')
            ->willReturn($collection);

        $this->valueCollection
            ->method('getListUris')
            ->willReturn(['uri']);

        $this->rdsCollectionRepository
            ->method('findAll')
            ->willReturn($this->valueCollection);

        $this->complexSearchService
            ->method('query')
            ->willReturn($queryBuilder);

        $this->complexSearchService
            ->method('searchType')
            ->willReturn($criteria);

        $this->complexSearchService
            ->method('getGateway')
            ->willReturn($gateway);

        $queryBuilder->method('setCriteria')
            ->willReturnSelf();

        $gateway->method('search')
            ->willReturn([$property]);

        $result = $this->subject->findAllUris(
            [
                'listUri' => 'uri1',
            ]
        );

        $this->assertSame(
            [
                'expectedUri',
            ],
            $result
        );
    }
}
