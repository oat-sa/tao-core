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
 * Copyright (c) 2022-2025 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\dataBinding;

use core_kernel_classes_Class;
use core_kernel_classes_ContainerCollection;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\event\EventManager;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use tao_models_classes_dataBinding_GenerisInstanceDataBinder;
use tao_models_classes_dataBinding_GenerisInstanceDataBindingException;

class GenerisInstanceDataBinderTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const URI_CLASS_TYPE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
    private const URI_PROPERTY_1 = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#p1';
    private const URI_PROPERTY_2 = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#p2';
    private const URI_TYPE_1 = 'http://example.com/Type1';
    private const URI_TYPE_2 = 'http://example.com/Type2';

    private tao_models_classes_dataBinding_GenerisInstanceDataBinder $sut;
    private core_kernel_classes_Resource|MockObject $target;
    private core_kernel_classes_Class|MockObject $classType1;
    private core_kernel_classes_Class|MockObject $classType2;
    private core_kernel_classes_Property|MockObject $property1;
    private core_kernel_classes_Property|MockObject $property2;
    private EventManager|MockObject $eventManagerMock;
    private core_kernel_classes_ContainerCollection|MockObject $emptyCollectionMock;
    private core_kernel_classes_ContainerCollection|MockObject $nonEmptyCollectionMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->createMock(EventManager::class);
        $widget = $this->createMock(core_kernel_classes_Property::class);

        $this->classType1 = $this->createMock(core_kernel_classes_Class::class);
        $this->classType1
            ->method('getUri')
            ->willReturn(self::URI_TYPE_1);

        $this->classType2 = $this->createMock(core_kernel_classes_Class::class);
        $this->classType2
            ->method('getUri')
            ->willReturn(self::URI_TYPE_2);

        $this->target = $this->createMock(
            core_kernel_classes_Resource::class
        );

        $this->target
            ->method('getClass')
            ->willReturnMap([
                [self::URI_TYPE_1, $this->classType1],
                [self::URI_TYPE_2, $this->classType2],
            ]);

        $this->property1 = $this->createMock(core_kernel_classes_Property::class);
        $this->property1
            ->method('getUri')
            ->willReturn(self::URI_PROPERTY_1);

        $this->property2 = $this->createMock(core_kernel_classes_Property::class);
        $this->property2
            ->method('getUri')
            ->willReturn(self::URI_PROPERTY_2);

        $this->property1
            ->method('getWidget')
            ->willReturn($widget);

        $this->property2
            ->method('getWidget')
            ->willReturn($widget);

        $this->target
            ->method('getUri')
            ->willReturn('http://test/resource');

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

        $this->sut = new tao_models_classes_dataBinding_GenerisInstanceDataBinder(
            $this->target
        );

        $featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);
        $featureFlagChecker
            ->method('isEnabled')
            ->willReturn(false);

        $ontology = $this->createMock(Ontology::class);
        $ontology
            ->method('getClass')
            ->willReturnMap([
                [self::URI_TYPE_1, $this->classType1],
                [self::URI_TYPE_2, $this->classType2],
            ]);
        $ontology
            ->method('getProperty')
            ->willReturnMap([
                [self::URI_PROPERTY_1, $this->property1],
                [self::URI_PROPERTY_2, $this->property2],
            ]);

        $this->sut->withEventManager($this->eventManagerMock);
        $this->sut->withServiceManager(
            $this->getServiceManagerMock(
                [
                    FeatureFlagChecker::class => $featureFlagChecker,
                    Ontology::SERVICE_ID => $ontology
                ]
            )
        );
    }

    public function testBindScalarWithPreviousValue(): void
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('trigger')
        ;
        $this->target
            ->expects($this->once())
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $class) {
                return $class->getUri() === self::URI_TYPE_1;
            }))
            ->willReturn(true);

        $this->target
            ->method('getTypes')
            ->willReturn([
                $this->classType1
            ]);

        $this->target
            ->method('getProperty')
            ->with(self::URI_PROPERTY_1)
            ->willReturn($this->property1);

        // There is a previous value for prop1 and its new value is a scalar:
        // The data binder should call editPropertyValues() on the resource.
        //
        $this->target
            ->method('getPropertyValuesCollection')
            ->willReturnCallback(function (core_kernel_classes_Property $property) {
                if ($property->getUri() === self::URI_PROPERTY_1) {
                    return $this->nonEmptyCollectionMock;
                }

                $this->fail('Unexpected property: ' . $property->getUri());
            });

        $this->target
            ->expects($this->once())
            ->method('editPropertyValues')
            ->with($this->callback(
                function (core_kernel_classes_Property $property, $value = null) {
                    return $property->getUri() === self::URI_PROPERTY_1;
                }
            ));

        // Binding a single class type and a single, non-empty value for
        // URI_PROPERTY_1, which should trigger editPropertyValues().
        //
        $resource = $this->sut->bind([
            self::URI_CLASS_TYPE => self::URI_TYPE_1,
            self::URI_PROPERTY_1 => 'Value 1'
        ]);

        $this->assertSame($this->target, $resource);
    }

    public function testBindScalarWithNoPreviousValue(): void
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('trigger');

        $this->target
            ->expects($this->once())
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $class) {
                return $class->getUri() === self::URI_TYPE_1;
            }))
            ->willReturn(true);

        $this->target
            ->method('getTypes')
            ->willReturn([
                $this->classType1
            ]);

        $this->target
            ->method('getProperty')
            ->with(self::URI_PROPERTY_1)
            ->willReturn($this->property1);

        // There is no previous value for prop1 and its new value is a scalar:
        // The data binder should call setPropertyValue() on the resource.
        //
        $this->target
            ->method('getPropertyValuesCollection')
            ->willReturnCallback(function (core_kernel_classes_Property $property) {
                if ($property->getUri() === self::URI_PROPERTY_1) {
                    return $this->emptyCollectionMock;
                }

                $this->fail('Unexpected property: ' . $property->getUri());
            });

        $this->target
            ->expects($this->once())
            ->method('setPropertyValue')
            ->with(
                $this->callback(
                    function (core_kernel_classes_Property $property, $value = null) {
                        return $property->getUri() === self::URI_PROPERTY_1;
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

        $this->assertSame($this->target, $resource);
    }

    public function testBindArrayWithPreviousValue(): void
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('trigger');

        $this->target
            ->expects($this->once())
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $class) {
                return $class->getUri() === self::URI_TYPE_1;
            }))
            ->willReturn(true);

        $this->target
            ->method('getTypes')
            ->willReturn([
                $this->classType1
            ]);

        $this->target
            ->method('getProperty')
            ->with(self::URI_PROPERTY_1)
            ->willReturn($this->property1);

        // There is a previous value for prop1 and its new value is an array:
        // The data binder should call setPropertyValue() on the resource, but
        // removePropertyValues should be called first.
        $this->target
            ->method('getPropertyValuesCollection')
            ->willReturnCallback(function (core_kernel_classes_Property $property) {
                if ($property->getUri() === self::URI_PROPERTY_1) {
                    return $this->nonEmptyCollectionMock;
                }

                $this->fail('Unexpected property: ' . $property->getUri());
            });

        $this->target
            ->expects($this->once())
            ->method('removePropertyValues')
            ->with($this->callback(
                function (core_kernel_classes_Property $propoerty) {
                    return $propoerty->getUri() === self::URI_PROPERTY_1;
                }
            ));

        $this->target
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

        $this->assertSame($this->target, $resource);
    }

    public function testBindArrayWithNoPreviousValue(): void
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('trigger');

        $this->target
            ->expects($this->once())
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $class) {
                return $class->getUri() === self::URI_TYPE_1;
            }))
            ->willReturn(true);

        $this->target
            ->method('getTypes')
            ->willReturn([
                $this->classType1
            ]);

        $this->target
            ->method('getProperty')
            ->with(self::URI_PROPERTY_1)
            ->willReturn($this->property1);

        // There is no previous value for prop1 and its new value is a scalar:
        // The data binder should call setPropertyValue() on the resource.
        $this->target
            ->method('getPropertyValuesCollection')
            ->willReturnCallback(function (core_kernel_classes_Property $property) {
                if ($property->getUri() === self::URI_PROPERTY_1) {
                    return $this->emptyCollectionMock;
                }

                $this->fail('Unexpected property: ' . $property->getUri());
            });

        $this->target
            ->expects($this->never())
            ->method('removePropertyValues');

        $this->target
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

        $this->assertSame($this->target, $resource);
    }

    public function testBindEmptyValue(): void
    {
        $this->eventManagerMock
            ->expects($this->exactly(2))
            ->method('trigger');

        $this->target
            ->expects($this->exactly(2))
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $class) {
                return $class->getUri() === self::URI_TYPE_1
                    || $class->getUri() === self::URI_TYPE_2;
            }))
            ->willReturn(true);

        $this->target
            ->method('getTypes')
            ->willReturn([
                $this->classType1,
                $this->classType2
            ]);

        $this->target
            ->method('getProperty')
            ->willReturnMap([
                [self::URI_PROPERTY_1, $this->property1],
                [self::URI_PROPERTY_2, $this->property2],
            ]);

        // There is a previous value for prop1 and its new value is empty:
        // The data binder will call removePropertyValues() for the property.
        $this->target
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (core_kernel_classes_Property $property) {
                    if ($property->getUri() === self::URI_PROPERTY_1) {
                        return $this->nonEmptyCollectionMock;
                    }
                    if ($property->getUri() === self::URI_PROPERTY_2) {
                        return $this->nonEmptyCollectionMock;
                    }

                    $this->fail('Unexpected property: ' . $property->getUri());
                }
            ));

        $this->target
            ->expects($this->exactly(1))
            ->method('editPropertyValues')
            ->willReturnCallback(
                function (core_kernel_classes_Property $property, $value = null) {
                    $this->assertEquals('Value 2', $value);
                    $this->assertEquals(self::URI_PROPERTY_2, $property->getUri());
                }
            );

        $this->target
            ->expects($this->exactly(1))
            ->method('removePropertyValues')
            ->willReturnCallback(
                function (core_kernel_classes_Property $property, $opts = []) {
                    $this->assertEquals(self::URI_PROPERTY_1, $property->getUri());
                }
            );

        // Binding multiple values for the class type, and an empty value for
        // URI_PROPERTY_1, which should trigger removePropertyValues().
        $resource = $this->sut->bind([
            self::URI_CLASS_TYPE => [self::URI_TYPE_1, self::URI_TYPE_2],
            self::URI_PROPERTY_1 => ' ',
            self::URI_PROPERTY_2 => 'Value 2',
        ]);

        $this->assertSame($this->target, $resource);
    }

    public function testBindNewTypesToExistingInstance(): void
    {
        $this->eventManagerMock
            ->expects($this->never())
            ->method('trigger');

        $this->target
            ->method('getTypes')
            ->willReturn([
                new core_kernel_classes_Class(self::URI_TYPE_1),
            ]);

        $this->target
            ->expects($this->exactly(1))
            ->method('removeType')
            ->with($this->callback(function (core_kernel_classes_Class $class) {
                return $class->getUri() === self::URI_TYPE_1;
            }))
            ->willReturn(true);

        $this->target
            ->expects($this->exactly(1))
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $class) {
                return $class->getUri() === self::URI_TYPE_2;
            }))
            ->willReturn(true);

        $this->target
            ->method('getProperty')
            ->willReturnMap([
                [self::URI_PROPERTY_1, $this->property1],
                [self::URI_PROPERTY_2, $this->property2],
            ]);

        // There are no properties other than types for this test
        $this->target
            ->expects($this->never())
            ->method('getPropertyValuesCollection');

        // Binding multiple values for the class type, and an empty value for
        // URI_PROPERTY_1, which should trigger removePropertyValues().
        $resource = $this->sut->bind([
            self::URI_CLASS_TYPE => [self::URI_TYPE_2],
        ]);

        $this->assertSame($this->target, $resource);
    }

    public function testDontSetNewValuesIfTheyAreEmpty(): void
    {
        // The event is triggered even if the property value stays the same
        $this->eventManagerMock
            ->expects($this->once())
            ->method('trigger');

        $this->target
            ->expects($this->never())
            ->method('setType');

        $this->target
            ->method('getTypes')
            ->willReturn([]);

        $this->target
            ->method('getProperty')
            ->with(self::URI_PROPERTY_1)
            ->willReturn($this->property1);

        $this->target
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (core_kernel_classes_Property $property) {
                    if ($property->getUri() === self::URI_PROPERTY_1) {
                        return $this->emptyCollectionMock;
                    }
                    if ($property->getUri() === self::URI_PROPERTY_2) {
                        return $this->emptyCollectionMock;
                    }

                    $this->fail('Unexpected property: ' . $property->getUri());
                }
            ));

        $this->target
            ->expects($this->never())
            ->method('editPropertyValues');

        $this->target
            ->expects($this->never())
            ->method('removePropertyValues');

        $this->target
            ->expects($this->never())
            ->method('setPropertyValue');

        // Binding an empty value for URI_PROPERTY_1 , which already has no
        // values, should not trigger setting, editing nor removal calls.
        $resource = $this->sut->bind([
            self::URI_PROPERTY_1 => '  ',
        ]);

        $this->assertSame($this->target, $resource);
    }

    public function testZeroIsNotHandledAsAnEmptyValue(): void
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('trigger');

        $this->target
            ->expects($this->never())
            ->method('setType');

        $this->target
            ->method('getTypes')
            ->willReturn([
                $this->classType1
            ]);

        $this->target
            ->method('getProperty')
            ->with(self::URI_PROPERTY_1)
            ->willReturn($this->property1);

        // There is no previous value for prop1 and its new value is a scalar:
        // The data binder should call setPropertyValue() on the resource.
        //
        $this->target
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (core_kernel_classes_Property $property) {
                    if ($property->getUri() === self::URI_PROPERTY_1) {
                        return $this->emptyCollectionMock;
                    }

                    $this->fail('Unexpected property: ' . $property->getUri());
                }
            ));

        $this->target
            ->expects($this->once())
            ->method('setPropertyValue')
            ->with(
                $this->callback(
                    function (core_kernel_classes_Property $property, $value = null) {
                        return $property->getUri() === self::URI_PROPERTY_1;
                    }
                ),
                '0'
            );

        $resource = $this->sut->bind([
            self::URI_PROPERTY_1 => '0'
        ]);

        $this->assertSame($this->target, $resource);
    }

    public function testExceptionsAreWrappedAndRethrown(): void
    {
        $this->eventManagerMock
            ->expects($this->never())
            ->method('trigger');

        $this->target
            ->expects($this->never())
            ->method('setType');

        $this->target
            ->method('getTypes')
            ->willReturn([
                $this->classType1
            ]);

        $this->target
            ->method('getProperty')
            ->with(self::URI_PROPERTY_1)
            ->willReturn($this->property1);

        $this->target
            ->method('getPropertyValuesCollection')
            ->willThrowException(new tao_models_classes_dataBinding_GenerisInstanceDataBindingException('error', 123));

        $this->expectException(
            tao_models_classes_dataBinding_GenerisInstanceDataBindingException::class
        );

        $this->expectExceptionMessage(
            "An error occured while binding property values to instance '': "
        );

        $resource = $this->sut->bind([
            self::URI_PROPERTY_2 => 'Value 2',
        ]);

        $this->assertSame($this->target, $resource);
    }
}
