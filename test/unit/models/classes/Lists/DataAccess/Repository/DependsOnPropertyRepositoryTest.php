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

use InvalidArgumentException;
use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use tao_helpers_form_elements_Combobox;
use PHPUnit\Framework\MockObject\MockObject;
use core_kernel_classes_ContainerCollection;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertyCollection;
use oat\tao\model\Lists\DataAccess\Repository\DependsOnPropertyRepository;
use oat\tao\model\Lists\Business\Specification\PrimaryPropertySpecification;
use oat\tao\model\Lists\Business\Specification\DependentPropertySpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListPropertySpecification;
use oat\tao\model\Lists\Business\Contract\DependsOnPropertyRepositoryInterface;
use oat\tao\model\Lists\Business\Contract\ParentPropertyListRepositoryInterface;

class DependsOnPropertyRepositoryTest extends TestCase
{
    /** @var DependsOnPropertyRepository */
    private $sut;

    /** @var FeatureFlagCheckerInterface|MockObject */
    private $featureFlagChecker;

    /** @var PrimaryPropertySpecification|MockObject */
    private $primaryPropertySpecification;

    /** @var RemoteListPropertySpecification|MockObject */
    private $remoteListPropertySpecification;

    /** @var DependentPropertySpecification|MockObject */
    private $dependentPropertySpecification;

    /** @var ParentPropertyListRepositoryInterface|MockObject */
    private $parentPropertyListRepository;

    protected function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);
        $this->primaryPropertySpecification = $this->createMock(PrimaryPropertySpecification::class);
        $this->remoteListPropertySpecification = $this->createMock(
            RemoteListPropertySpecification::class
        );
        $this->dependentPropertySpecification = $this->createMock(DependentPropertySpecification::class);
        $this->parentPropertyListRepository = $this->createMock(
            ParentPropertyListRepositoryInterface::class
        );

        $this->sut = new DependsOnPropertyRepository(
            $this->featureFlagChecker,
            $this->primaryPropertySpecification,
            $this->remoteListPropertySpecification,
            $this->dependentPropertySpecification,
            $this->parentPropertyListRepository
        );
    }

    public function testWithDisabledFeatureFlag(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->primaryPropertySpecification
            ->expects($this->never())
            ->method('isSatisfiedBy');

        $property = $this->createMock(core_kernel_classes_Property::class);

        $this->assertEquals(
            new DependsOnPropertyCollection(),
            $this->sut->findAll([DependsOnPropertyRepositoryInterface::FILTER_PROPERTY => $property])
        );
    }

    public function testFindAllWithPrimaryProperty(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->primaryPropertySpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->expects($this->never())
            ->method('getWidget');
        $property
            ->expects($this->never())
            ->method('getDomain');

        $this->assertEquals(
            new DependsOnPropertyCollection(),
            $this->sut->findAll([DependsOnPropertyRepositoryInterface::FILTER_PROPERTY => $property])
        );
    }

    public function testFindAllWithEmptyDomain(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->primaryPropertySpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->remoteListPropertySpecification
            ->expects($this->never())
            ->method('isSatisfiedBy');

        $this->dependentPropertySpecification
            ->expects($this->never())
            ->method('isSatisfiedBy');

        $this->sut->withProperties([]);
        $propertiesCollection = $this->sut->findAll(
            [
                DependsOnPropertyRepositoryInterface::FILTER_PROPERTY => $this->createProperty('uri', 0),
            ]
        );

        $this->assertEquals(new DependsOnPropertyCollection(), $propertiesCollection);
        $this->assertEquals(0, $propertiesCollection->count());
    }

    public function testFindAllWithoutPropertiesAndClass(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('class or property filter need to be provided');

        $this->sut->findAll([]);
    }

    public function testFindAllWithoutValidProperty(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $parentUri = 'parentUri';
        $parentProperty = $this->createProperty($parentUri);

        $this->parentPropertyListRepository
            ->expects($this->once())
            ->method('findAllUris');

        $this->primaryPropertySpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

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
                DependsOnPropertyRepositoryInterface::FILTER_PROPERTY => $this->createProperty('propertyUri'),
                DependsOnPropertyRepositoryInterface::FILTER_LIST_URI => 'uri1',
            ]
        );

        $this->assertEquals(0, $propertiesCollection->count());
    }

    public function testFindAllWithOneValidProperty(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

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

        $this->primaryPropertySpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

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
                DependsOnPropertyRepositoryInterface::FILTER_PROPERTY => $property,
                DependsOnPropertyRepositoryInterface::FILTER_LIST_URI => 'uri1',
            ]
        );
        $this->assertEquals(1, $propertiesCollection->count());
        $this->assertEquals($parentProperty, $propertiesCollection->offsetGet(0)->getProperty());
    }

    public function testFindAllWithClassOnly(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

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

        $this->primaryPropertySpecification
            ->expects($this->never())
            ->method('isSatisfiedBy');

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
                DependsOnPropertyRepositoryInterface::FILTER_CLASS => $class,
                DependsOnPropertyRepositoryInterface::FILTER_LIST_URI => 'uri1',
            ]
        );
        $this->assertEquals(1, $propertiesCollection->count());
        $this->assertEquals($parentProperty, $propertiesCollection->offsetGet(0)->getProperty());
    }

    private function createProperty(string $uri, int $collectionSize = 1): core_kernel_classes_Property
    {
        $domainCollection = $this->createMock(core_kernel_classes_ContainerCollection::class);

        $domainCollection
            ->method('count')
            ->willReturn($collectionSize);

        if ($collectionSize) {
            $domainCollection
                ->method('get')
                ->willReturn($this->createMock(core_kernel_classes_Class::class));
        }

        $property = $this->createMock(core_kernel_classes_Property::class);
        $widget = $this->createMock(core_kernel_classes_Resource::class);

        $property
            ->method('getDomain')
            ->willReturn($domainCollection);

        $property
            ->method('getUri')
            ->willReturn($uri);

        $property
            ->method('getWidget')
            ->willReturn($widget);

        $widget
            ->method('getUri')
            ->willReturn(tao_helpers_form_elements_Combobox::WIDGET_ID);

        return $property;
    }
}
