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

use oat\generis\test\TestCase;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_ContainerCollection;
use oat\tao\model\Lists\Business\Contract\ParentPropertyListRepositoryInterface;
use oat\tao\model\Lists\DataAccess\Repository\ParentPropertyListCachedRepository;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertyCollection;
use oat\tao\model\Lists\DataAccess\Repository\DependsOnPropertyRepository;
use oat\tao\model\Lists\Business\Specification\DependentPropertySpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListPropertySpecification;

class DependsOnPropertyRepositoryTest extends TestCase
{
    /** @var DependsOnPropertyRepository */
    private $sut;

    /** @var RemoteListPropertySpecification|MockObject */
    private $remoteListPropertySpecification;

    /** @var DependentPropertySpecification|MockObject */
    private $dependentPropertySpecification;

    /** @var core_kernel_classes_Property|MockObject */
    private $property;

    /** @var core_kernel_classes_ContainerCollection|MockObject */
    private $domainCollection;

    /** @var ParentPropertyListRepositoryInterface|MockObject */
    private $parentPropertyListRepository;

    public function setUp(): void
    {
        $this->remoteListPropertySpecification = $this->createMock(RemoteListPropertySpecification::class);
        $this->dependentPropertySpecification = $this->createMock(DependentPropertySpecification::class);
        $this->parentPropertyListRepository = $this->createMock(ParentPropertyListRepositoryInterface::class);

        $this->sut = new DependsOnPropertyRepository();
        $this->sut->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    RemoteListPropertySpecification::class => $this->remoteListPropertySpecification,
                    DependentPropertySpecification::class => $this->dependentPropertySpecification,
                    ParentPropertyListCachedRepository::class => $this->parentPropertyListRepository,
                ]
            )
        );

        $this->property = $this->createMock(core_kernel_classes_Property::class);
        $this->domainCollection = $this->createMock(core_kernel_classes_ContainerCollection::class);
    }

    public function testFindAllWithEmptyDomain(): void
    {
        $this->remoteListPropertySpecification
            ->expects($this->never())
            ->method('isSatisfiedBy');
        $this->dependentPropertySpecification
            ->expects($this->never())
            ->method('isSatisfiedBy');

        $this->domainCollection
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->domainCollection
            ->expects($this->never())
            ->method('get');

        $this->property
            ->expects($this->once())
            ->method('getDomain')
            ->willReturn($this->domainCollection);

        $this->sut->withProperties([]);
        $propertiesCollection = $this->sut->findAll(['property' => $this->property]);

        $this->assertEquals(new DependsOnPropertyCollection(), $propertiesCollection);
        $this->assertEquals(0, $propertiesCollection->count());
    }

    public function testFindAllWithoutProperties(): void
    {
        $this->parentPropertyListRepository
            ->expects($this->once())
            ->method('findAllUris')
            ->willReturn(
                [
                    'parentUri1',
                ]
            );

        $this->remoteListPropertySpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->dependentPropertySpecification
            ->expects($this->never())
            ->method('isSatisfiedBy');

        $this->domainCollection
            ->method('count')
            ->willReturn(1);

        $this->domainCollection
            ->method('get')
            ->willReturn($this->createMock(core_kernel_classes_Class::class));

        $this->property
            ->expects($this->exactly(2))
            ->method('getDomain')
            ->willReturn($this->domainCollection);

        $this->sut->withProperties([]);
        $propertiesCollection = $this->sut->findAll(
            [
                'property' => $this->property
            ]
        );

        $this->assertEquals(new DependsOnPropertyCollection(), $propertiesCollection);
        $this->assertEquals(0, $propertiesCollection->count());
    }

    /**
     * @dataProvider getDataForFindAllWithPropertiesTest
     */
    public function testFindAllWithProperties(
        array $properties,
        int $expectedRemoteListPropertySpecificationCalls,
        int $expectedDependentPropertySpecificationCalls,
        int $expectedCollectionCount
    ): void {
        $this->remoteListPropertySpecification
            ->expects($this->exactly($expectedRemoteListPropertySpecificationCalls))
            ->method('isSatisfiedBy')
            ->willReturn(true);
        $this->dependentPropertySpecification
            ->expects($this->exactly($expectedDependentPropertySpecificationCalls))
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->domainCollection
            ->method('count')
            ->willReturn(1);
        $this->domainCollection
            ->method('get')
            ->willReturn($this->createMock(core_kernel_classes_Class::class));

        $this->property = $this->createMock(core_kernel_classes_Property::class);
        $this->property
            ->expects($this->exactly(2))
            ->method('getDomain')
            ->willReturn($this->domainCollection);
        $this->property
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('propertyUri');

        $this->sut->withProperties($properties);
        $propertiesCollection = $this->sut->findAll(['property' => $this->property]);

        $this->assertNotEquals(new DependsOnPropertyCollection(), $propertiesCollection);
        $this->assertEquals($expectedCollectionCount, $propertiesCollection->count());
    }

    public function getDataForFindAllWithPropertiesTest(): array
    {
        return [
            'One valid property' => [
                'properties' => [
                    $this->createProperty('firstPropertyUri'),
                ],
                'expectedRemoteListPropertySpecificationCalls' => 2,
                'expectedDependentPropertySpecificationCalls' => 1,
                'expectedCollectionCount' => 1,
            ],
            'Two valid properties' => [
                'properties' => [
                    $this->createProperty('firstPropertyUri'),
                    $this->createProperty('secondPropertyUri'),
                ],
                'expectedRemoteListPropertySpecificationCalls' => 3,
                'expectedDependentPropertySpecificationCalls' => 2,
                'expectedCollectionCount' => 2,
            ],
            'Two valid properties and one with the same uri' => [
                'properties' => [
                    $this->createProperty('firstPropertyUri'),
                    $this->createProperty('secondPropertyUri'),
                    $this->createProperty('propertyUri'),
                ],
                'expectedRemoteListPropertySpecificationCalls' => 3,
                'expectedDependentPropertySpecificationCalls' => 2,
                'expectedCollectionCount' => 2,
            ],
        ];
    }

    private function createProperty(string $uri): core_kernel_classes_Property
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        return $property;
    }
}
