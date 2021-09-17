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
use InvalidArgumentException;
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
    }

    public function testFindAllWithEmptyDomain(): void
    {
        $this->remoteListPropertySpecification
            ->expects($this->never())
            ->method('isSatisfiedBy');

        $this->dependentPropertySpecification
            ->expects($this->never())
            ->method('isSatisfiedBy');

        $this->sut->withProperties([]);
        $propertiesCollection = $this->sut->findAll(
            [
                'property' => $this->createProperty('uri', 0),
            ]
        );

        $this->assertEquals(new DependsOnPropertyCollection(), $propertiesCollection);
        $this->assertEquals(0, $propertiesCollection->count());
    }

    public function testFindAllWithoutPropertiesAndClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('class or property filter need to be provided');

        $this->sut->findAll([]);
    }

    public function testFindAllWithoutValidProperty(): void
    {
        $parentUri = 'parentUri';
        $parentProperty = $this->createProperty($parentUri);

        $this->parentPropertyListRepository
            ->expects($this->once())
            ->method('findAllUris');

        $this->remoteListPropertySpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->dependentPropertySpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->sut->withProperties(
            [
                $parentProperty,
                $parentProperty,
            ]
        );

        $propertiesCollection = $this->sut->findAll(
            [
                'property' => $this->createProperty('propertyUri'),
                'listUri'  => "uri1"
            ]
        );

        $this->assertEquals(0, $propertiesCollection->count());
    }

    public function testFindAllWithOneValidProperty(): void
    {
        $parentUri = 'parentUri';
        $parentProperty = $this->createProperty($parentUri);

        $this->parentPropertyListRepository
            ->expects($this->once())
            ->method('findAllUris')
            ->willReturn(
                [
                    $parentUri,
                ]
            );

        $this->remoteListPropertySpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);
        $property = $this->createProperty('propertyUri');

        $this->sut->withProperties(
            [
                $parentProperty,
            ]
        );

        $propertiesCollection = $this->sut->findAll(
            [
                'property' => $property,
                'listUri'  => "uri1"
            ]
        );
        $this->assertEquals(1, $propertiesCollection->count());
        $this->assertEquals($parentProperty, $propertiesCollection->offsetGet(0)->getProperty());
    }

    public function testFindAllWithClassOnly(): void
    {
        $parentUri = 'parentUri';
        $parentProperty = $this->createProperty($parentUri);

        $this->parentPropertyListRepository
            ->expects($this->once())
            ->method('findAllUris')
            ->willReturn(
                [
                    $parentUri,
                ]
            );

        $this->remoteListPropertySpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);
        $class = $this->createMock(core_kernel_classes_Class::class);

        $this->sut->withProperties(
            [
                $parentProperty,
            ]
        );

        $propertiesCollection = $this->sut->findAll(
            [
                'class' => $class,
                'listUri'  => "uri1"
            ]
        );
        $this->assertEquals(1, $propertiesCollection->count());
        $this->assertEquals($parentProperty, $propertiesCollection->offsetGet(0)->getProperty());
    }

    private function createProperty(string $uri, int $collectionSize = 1): core_kernel_classes_Property
    {
        $domainCollection = $this->createMock(core_kernel_classes_ContainerCollection::class);

        $domainCollection->method('count')
            ->willReturn($collectionSize);

        if ($collectionSize) {
            $domainCollection->method('get')
                ->willReturn($this->createMock(core_kernel_classes_Class::class));
        }

        $property = $this->createMock(core_kernel_classes_Property::class);

        $property->method('getDomain')
            ->willReturn($domainCollection);

        $property->method('getUri')
            ->willReturn($uri);

        return $property;
    }
}
