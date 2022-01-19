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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\action;

use common_Object;
use core_kernel_classes_Resource;
use core_kernel_classes_Class;
use core_kernel_classes_ContainerCollection;
use core_kernel_classes_Property;
use oat\generis\test\MockObject;
use oat\generis\test\OntologyMockTrait;
use oat\generis\test\TestCase;
use oat\oatbox\event\EventManager;
use oat\tao\model\event\MetadataModified;
use tao_models_classes_dataBinding_GenerisInstanceDataBinder;
use tao_models_classes_dataBinding_GenerisInstanceDataBindingException;

class GenerisInstanceDataBinderTest extends TestCase
{
    use OntologyMockTrait;

    private const URI_CLASS_TYPE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
    private const URI_PROPERTY_1 = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#p1';
    private const URI_PROPERTY_2 = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#p2';
    private const URI_TYPE_1 = 'http://test.com/Type1';
    private const URI_TYPE_2 = 'http://test.com/Type2';

    /** @var tao_models_classes_dataBinding_GenerisInstanceDataBinder */
    private $sut;

    /** @var core_kernel_classes_Resource|MockObject */
    private $resource;

    /** @var EventManager|MockObject */
    private $eventManagerMock;

    /** @var core_kernel_classes_ContainerCollection|MockObject */
    private $emptyCollectionMock;

    /** @var core_kernel_classes_ContainerCollection|MockObject */
    private $nonEmptyCollectionMock;

    public function setUp(): void
    {
        $this->eventManagerMock = $this->createMock(EventManager::class);

        $this->resource = $this->createMock(
            core_kernel_classes_Resource::class
        );
        $this->resource->setModel($this->getOntologyMock());

        $this->nonEmptyCollectionMock = $this->createMock(
            core_kernel_classes_ContainerCollection::class
        );

        $this->nonEmptyCollectionMock
            ->method('count')
            ->willReturn(1);

        $this->emptyCollectionMock = $this->createMock(
            core_kernel_classes_ContainerCollection::class
        );

        $this->emptyCollectionMock
            ->method('count')
            ->willReturn(0);

        $this->sut =
            new tao_models_classes_dataBinding_GenerisInstanceDataBinder(
                $this->resource,
                $this->eventManagerMock
            );
    }

    public function testBindScalarWithPreviousValue(): void
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('trigger')
            ->with($this->callback(function (MetadataModified $e): bool {
                return (
                    $e->getResource()->getLabel() == $this->resource->getUri()
                    && ($e->getMetadataUri() == self::URI_PROPERTY_1)
                    && ($e->getMetadataValue() == 'Value 1'));
            }));

        $this->resource
            ->expects($this->once())
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $c) {
                return ($c->getUri() == self::URI_TYPE_1);
            }))
            ->willReturn(true);

        $this->resource
            ->method('getTypes')
            ->willReturn([
                new core_kernel_classes_Class(self::URI_TYPE_1)
            ]);

        // There is a previous value for prop1 and its new value is a scalar:
        // The data binder should call editPropertyValues() on the resource.
        //
        $this->resource
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (core_kernel_classes_Property $property) {
                    if ($property->getUri() == self::URI_PROPERTY_1) {
                        return $this->nonEmptyCollectionMock;
                    }

                    $this->fail("Unexpected property: {$property->getUri()}");
                }
            ));

        $this->resource
            ->expects($this->once())
            ->method('editPropertyValues')
            ->with($this->callback(
                function (core_kernel_classes_Property $property, $_ = null) {
                    return ($property->getUri() == self::URI_PROPERTY_1);
                }
            ));

        // Binding a single class type and a single, non-empty value for
        // URI_PROPERTY_1, which should trigger editPropertyValues().
        //
        $resource = $this->sut->bind([
            self::URI_CLASS_TYPE => self::URI_TYPE_1,
            self::URI_PROPERTY_1 => 'Value 1'
        ]);

        $this->assertIsTheFormerInstance($resource);
    }

    public function testBindScalarWithNoPreviousValue(): void
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('trigger')
            ->with($this->callback(function (MetadataModified $e): bool {
                return (
                    $e->getResource()->getLabel() == $this->resource->getUri()
                    && ($e->getMetadataUri() == self::URI_PROPERTY_1)
                    && ($e->getMetadataValue() == 'Value 1'));
            }));

        $this->resource
            ->expects($this->once())
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $c) {
                return ($c->getUri() == self::URI_TYPE_1);
            }))
            ->willReturn(true);

        $this->resource
            ->method('getTypes')
            ->willReturn([
                new core_kernel_classes_Class(self::URI_TYPE_1)
            ]);

        // There is no previous value for prop1 and its new value is a scalar:
        // The data binder should call setPropertyValue() on the resource.
        //
        $this->resource
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (core_kernel_classes_Property $property) {
                    if ($property->getUri() == self::URI_PROPERTY_1) {
                        return $this->emptyCollectionMock;
                    }

                    $this->fail("Unexpected property: {$property->getUri()}");
                }
            ));

        $this->resource
            ->expects($this->once())
            ->method('setPropertyValue')
            ->with(
                $this->callback(
                    function (core_kernel_classes_Property $property, $_ = null) {
                        return ($property->getUri() == self::URI_PROPERTY_1);
                    }
                ),
                'Value 1'
            );

        // Binding a single class type and a single, non-empty value for
        // URI_PROPERTY_1 (which doesn't have a previous value), which should
        // trigger setPropertyValue().
        //
        $resource = $this->sut->bind([
            self::URI_CLASS_TYPE => self::URI_TYPE_1,
            self::URI_PROPERTY_1 => 'Value 1'
        ]);

        $this->assertIsTheFormerInstance($resource);
    }

    public function testBindArrayWithPreviousValue(): void
    {
        $this->eventManagerMock
            ->expects($this->at(0))
            ->method('trigger')
            ->with($this->callback(function (MetadataModified $e): bool {
                return (
                    $e->getResource()->getLabel() == $this->resource->getUri()
                    && ($e->getMetadataUri() == self::URI_PROPERTY_1)
                    && ($e->getMetadataValue() == ['one', 'two']));
            }));

        $this->resource
            ->expects($this->once())
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $c) {
                return ($c->getUri() == self::URI_TYPE_1);
            }))
            ->willReturn(true);

        $this->resource
            ->method('getTypes')
            ->willReturn([
                new core_kernel_classes_Class(self::URI_TYPE_1)
            ]);

        // There is a previous value for prop1 and its new value is an array:
        // The data binder should call setPropertyValue() on the resource, but
        // removePropertyValues should be called first.
        $this->resource
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (core_kernel_classes_Property $property) {
                    if ($property->getUri() == self::URI_PROPERTY_1) {
                        return $this->nonEmptyCollectionMock;
                    }

                    $this->fail("Unexpected property: {$property->getUri()}");
                }
            ));

        $this->resource
            ->expects($this->once())
            ->method('removePropertyValues')
            ->with($this->callback(function (core_kernel_classes_Property $p) {
                return ($p->getUri() == self::URI_PROPERTY_1);
            }));

        $this->resource
            ->expects($this->exactly(2))
            ->method('setPropertyValue')
            ->withConsecutive(
                [$this->anything(), 'one'],
                [$this->anything(), 'two']
            );

        $resource = $this->sut->bind([
            self::URI_CLASS_TYPE => self::URI_TYPE_1,
            self::URI_PROPERTY_1 => ['one', 'two']
        ]);

        $this->assertIsTheFormerInstance($resource);
    }

    public function testBindArrayWithNoPreviousValue(): void
    {
        $this->eventManagerMock
            ->expects($this->at(0))
            ->method('trigger')
            ->with($this->callback(function (MetadataModified $e): bool {
                return (
                    $e->getResource()->getLabel() == $this->resource->getUri()
                    && ($e->getMetadataUri() == self::URI_PROPERTY_1)
                    && ($e->getMetadataValue() == ['Value 1', 'Value 2']));
            }));

        $this->resource
            ->expects($this->once())
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $c) {
                return ($c->getUri() == self::URI_TYPE_1);
            }))
            ->willReturn(true);

        $this->resource
            ->method('getTypes')
            ->willReturn([
                new core_kernel_classes_Class(self::URI_TYPE_1)
            ]);

        // There is no previous value for prop1 and its new value is a scalar:
        // The data binder should call setPropertyValue() on the resource.
        $this->resource
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (core_kernel_classes_Property $property) {
                    if ($property->getUri() == self::URI_PROPERTY_1) {
                        return $this->emptyCollectionMock;
                    }

                    $this->fail("Unexpected property: {$property->getUri()}");
                }
            ));

        $this->resource
            ->expects($this->never())
            ->method('removePropertyValues');

        $this->resource
            ->expects($this->exactly(2))
            ->method('setPropertyValue')
            ->withConsecutive(
                [$this->anything(), 'Value 1'],
                [$this->anything(), 'Value 2']
            );

        // Binding a single class type and a single, non-empty value for
        // URI_PROPERTY_1 (which doesn't have a previous value), which should
        // trigger setPropertyValue().
        $resource = $this->sut->bind([
            self::URI_CLASS_TYPE => self::URI_TYPE_1,
            self::URI_PROPERTY_1 => ['Value 1', 'Value 2']
        ]);

        $this->assertIsTheFormerInstance($resource);
    }

    public function testBindEmptyValue(): void
    {
        $this->eventManagerMock
            ->expects($this->at(0))
            ->method('trigger')
            ->with($this->callback(function (MetadataModified $e): bool {
                return (
                    $e->getResource()->getLabel() == $this->resource->getUri()
                    && ($e->getMetadataUri() == self::URI_PROPERTY_1)
                    && ($e->getMetadataValue() == ' '));
            }));

        $this->eventManagerMock
            ->expects($this->at(1))
            ->method('trigger')
            ->with($this->callback(function (MetadataModified $e): bool {
                return (
                    $e->getResource()->getLabel() == $this->resource->getUri()
                    && ($e->getMetadataUri() == self::URI_PROPERTY_2)
                    && ($e->getMetadataValue() == 'Value 2'));
            }));

        $this->resource
            ->expects($this->exactly(2))
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $c) {
                return ($c->getUri() == self::URI_TYPE_1)
                    || ($c->getUri() == self::URI_TYPE_2);
            }))
            ->willReturn(true);

        $this->resource
            ->method('getTypes')
            ->willReturn([
                new core_kernel_classes_Class(self::URI_TYPE_1),
                new core_kernel_classes_Class(self::URI_TYPE_2)
            ]);

        // There is a previous value for prop1 and its new value is empty:
        // The data binder will call removePropertyValues() for the property.
        $this->resource
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (core_kernel_classes_Property $property) {
                    if ($property->getUri() == self::URI_PROPERTY_1) {
                        return $this->nonEmptyCollectionMock;
                    }
                    if ($property->getUri() == self::URI_PROPERTY_2) {
                        return $this->nonEmptyCollectionMock;
                    }

                    $this->fail("Unexpected property: {$property->getUri()}");
                }
            ));

        $this->resource
            ->expects($this->exactly(1))
            ->method('editPropertyValues')
            ->willReturnCallback(
                function (core_kernel_classes_Property $p, $v = null) {
                    $this->assertEquals('Value 2', $v);
                    $this->assertEquals(self::URI_PROPERTY_2, $p->getUri());
                }
            );

        $this->resource
            ->expects($this->exactly(1))
            ->method('removePropertyValues')
            ->willReturnCallback(
                function (core_kernel_classes_Property $p, $opts = []) {
                    $this->assertEquals(self::URI_PROPERTY_1, $p->getUri());
                }
            );

        // Binding multiple values for the class type, and an empty value for
        // URI_PROPERTY_1, which should trigger removePropertyValues().
        $resource = $this->sut->bind([
            self::URI_CLASS_TYPE => [self::URI_TYPE_1, self::URI_TYPE_2],
            self::URI_PROPERTY_1 => ' ',
            self::URI_PROPERTY_2 => 'Value 2',
        ]);

        $this->assertIsTheFormerInstance($resource);
    }

    public function testBindNewTypesToExistingInstance(): void
    {
        $this->eventManagerMock
            ->expects(self::never())
            ->method('trigger');

        $this->resource
            ->method('getTypes')
            ->willReturn([
                new core_kernel_classes_Class(self::URI_TYPE_1),
            ]);

        $this->resource
            ->expects(self::exactly(1))
            ->method('removeType')
            ->with($this->callback(function (core_kernel_classes_Class $c) {
                return ($c->getUri() == self::URI_TYPE_1);
            }))
            ->willReturn(true);

        $this->resource
            ->expects(self::exactly(1))
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $c) {
                return ($c->getUri() == self::URI_TYPE_2);
            }))
            ->willReturn(true);

        // There are no properties other than types for this test
        $this->resource
            ->expects(self::never())
            ->method('getPropertyValuesCollection');

        // Binding multiple values for the class type, and an empty value for
        // URI_PROPERTY_1, which should trigger removePropertyValues().
        $resource = $this->sut->bind([
            self::URI_CLASS_TYPE => [self::URI_TYPE_2],
        ]);

        $this->assertIsTheFormerInstance($resource);
    }

    public function testDontSetNewValuesIfTheyAreEmpty(): void
    {
        // The event is triggered even if the property value stays the same
        $this->eventManagerMock
            ->expects($this->at(0))
            ->method('trigger')
            ->with($this->callback(function (MetadataModified $e): bool {
                return (
                    $e->getResource()->getLabel() == $this->resource->getUri()
                    && ($e->getMetadataUri() == self::URI_PROPERTY_1)
                    && ($e->getMetadataValue() == '  '));
            }));

        $this->resource
            ->expects($this->never())
            ->method('setType');

        $this->resource
            ->method('getTypes')
            ->willReturn([]);

        $this->resource
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (core_kernel_classes_Property $property) {
                    if ($property->getUri() == self::URI_PROPERTY_1) {
                        return $this->emptyCollectionMock;
                    }
                    if ($property->getUri() == self::URI_PROPERTY_2) {
                        return $this->emptyCollectionMock;
                    }

                    $this->fail("Unexpected property: {$property->getUri()}");
                }
            ));

        $this->resource
            ->expects($this->never())
            ->method('editPropertyValues');

        $this->resource
            ->expects($this->never())
            ->method('removePropertyValues');

        $this->resource
            ->expects($this->never())
            ->method('setPropertyValue');

        // Binding an empty value for URI_PROPERTY_1 , which already has no
        // values, should not trigger setting, editing nor removal calls.
        $resource = $this->sut->bind([
            self::URI_PROPERTY_1 => '  ',
        ]);

        $this->assertIsTheFormerInstance($resource);
    }

    public function testExceptionsAreWrappedAndRethrown(): void
    {
        $this->eventManagerMock
            ->expects($this->never())
            ->method('trigger');

        $this->resource
            ->expects($this->never())
            ->method('setType');

        $this->resource
            ->method('getTypes')
            ->willReturn([
                new core_kernel_classes_Class(self::URI_TYPE_1)
            ]);

        $this->resource
            ->method('getPropertyValuesCollection')
            ->willThrowException(new \Exception("error", 123));

        $this->expectException(
            tao_models_classes_dataBinding_GenerisInstanceDataBindingException::class
        );

        $this->expectExceptionMessage(
            "Error binding property values to instance"
        );

        $resource = $this->sut->bind([
            self::URI_PROPERTY_2 => 'Value 2',
        ]);

        $this->assertIsTheFormerInstance($resource);
    }

    private function assertIsTheFormerInstance($resource): void
    {
        // Used to check that the binder returns the former instance with
        // the changes applied
        $this->assertSame($this->resource, $resource);
    }
}
