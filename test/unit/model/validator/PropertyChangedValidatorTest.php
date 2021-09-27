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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

use oat\generis\test\TestCase;
use oat\tao\model\dto\OldProperty;
use oat\tao\model\validator\PropertyChangedValidator;
use oat\generis\model\resource\DependsOnPropertyCollection;

class PropertyChangedValidatorTest extends TestCase
{
    /** @var PropertyChangedValidator  */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new PropertyChangedValidator();
    }

    public function testDoNotTriggerIfDoesNotHaveChanges(): void
    {
        $propertyType = $this->createMock(core_kernel_classes_Resource::class);
        $propertyType
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('propertyTypeUri');

        $range = $this->createMock(core_kernel_classes_Class::class);
        $range
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('rangeUri');

        $dependsOnPropertyCollection = $this->createMock(DependsOnPropertyCollection::class);
        $dependsOnPropertyCollection
            ->expects($this->once())
            ->method('isEqual')
            ->willReturn(true);

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('propertyLabel');
        $property
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));
        $property
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn($propertyType);
        $property
            ->expects($this->once())
            ->method('getRange')
            ->willReturn($range);
        $property
            ->expects($this->once())
            ->method('getPropertyValues')
            ->willReturn([]);
        $property
            ->expects($this->once())
            ->method('getDependsOnPropertyCollection')
            ->willReturn($dependsOnPropertyCollection);

        $oldProperty = $this->createMock(OldProperty::class);
        $oldProperty
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('propertyLabel');
        $oldProperty
            ->expects($this->once())
            ->method('getPropertyType')
            ->willReturn($propertyType);
        $oldProperty
            ->expects($this->once())
            ->method('getRangeUri')
            ->willReturn('rangeUri');
        $oldProperty
            ->expects($this->once())
            ->method('getValidationRules')
            ->willReturn([]);
        $oldProperty
            ->expects($this->once())
            ->method('getDependsOnPropertyCollection')
            ->willReturn($dependsOnPropertyCollection);

        $this->assertFalse($this->sut->isPropertyChanged($property, $oldProperty));
    }

    public function testDoNotTriggerIfDoesNotHavePropertyType(): void
    {
        $range = $this->createMock(core_kernel_classes_Class::class);
        $range
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('rangeUri');

        $dependsOnPropertyCollection = $this->createMock(DependsOnPropertyCollection::class);
        $dependsOnPropertyCollection
            ->expects($this->once())
            ->method('isEqual')
            ->willReturn(true);

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('propertyLabel');
        $property
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));
        $property
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn(null);
        $property
            ->expects($this->once())
            ->method('getRange')
            ->willReturn($range);
        $property
            ->expects($this->once())
            ->method('getPropertyValues')
            ->willReturn([]);
        $property
            ->expects($this->once())
            ->method('getDependsOnPropertyCollection')
            ->willReturn($dependsOnPropertyCollection);

        $oldProperty = $this->createMock(OldProperty::class);
        $oldProperty
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('propertyLabel');
        $oldProperty
            ->expects($this->once())
            ->method('getPropertyType')
            ->willReturn(null);
        $oldProperty
            ->expects($this->once())
            ->method('getRangeUri')
            ->willReturn('rangeUri');
        $oldProperty
            ->expects($this->once())
            ->method('getValidationRules')
            ->willReturn([]);
        $oldProperty
            ->expects($this->once())
            ->method('getDependsOnPropertyCollection')
            ->willReturn($dependsOnPropertyCollection);

        $this->assertFalse($this->sut->isPropertyChanged($property, $oldProperty));
    }

    public function testTriggerIfHaveCurrentPropertyTypeButDoesNotHaveOldPropertyType(): void
    {
        $propertyType = $this->createMock(core_kernel_classes_Resource::class);
        $propertyType
            ->expects($this->never())
            ->method('getUri');

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('propertyLabel');
        $property
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));
        $property
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn($propertyType);
        $property
            ->expects($this->never())
            ->method('getRange');
        $property
            ->expects($this->never())
            ->method('getPropertyValues');
        $property
            ->expects($this->never())
            ->method('getDependsOnPropertyCollection');

        $oldProperty = $this->createMock(OldProperty::class);
        $oldProperty
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('propertyLabel');
        $oldProperty
            ->expects($this->once())
            ->method('getPropertyType')
            ->willReturn(null);
        $oldProperty
            ->expects($this->never())
            ->method('getRangeUri');
        $oldProperty
            ->expects($this->never())
            ->method('getValidationRules');
        $oldProperty
            ->expects($this->never())
            ->method('getDependsOnPropertyCollection');

        $this->assertTrue($this->sut->isPropertyChanged($property, $oldProperty));
    }

    public function testTriggerIfDoesNotHaveCurrentPropertyTypeButHaveOldPropertyType(): void
    {
        $propertyType = $this->createMock(core_kernel_classes_Resource::class);
        $propertyType
            ->expects($this->never())
            ->method('getUri');

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('propertyLabel');
        $property
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));
        $property
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn(null);
        $property
            ->expects($this->never())
            ->method('getRange');
        $property
            ->expects($this->never())
            ->method('getPropertyValues');
        $property
            ->expects($this->never())
            ->method('getDependsOnPropertyCollection');

        $oldProperty = $this->createMock(OldProperty::class);
        $oldProperty
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('propertyLabel');
        $oldProperty
            ->expects($this->once())
            ->method('getPropertyType')
            ->willReturn($propertyType);
        $oldProperty
            ->expects($this->never())
            ->method('getRangeUri');
        $oldProperty
            ->expects($this->never())
            ->method('getValidationRules');
        $oldProperty
            ->expects($this->never())
            ->method('getDependsOnPropertyCollection');

        $this->assertTrue($this->sut->isPropertyChanged($property, $oldProperty));
    }

    public function testTriggerIfHasChangesOnLabel(): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('newPropertyLabel');
        $property
            ->expects($this->never())
            ->method('getProperty');
        $property
            ->expects($this->never())
            ->method('getOnePropertyValue');
        $property
            ->expects($this->never())
            ->method('getRange');
        $property
            ->expects($this->never())
            ->method('getPropertyValues');
        $property
            ->expects($this->never())
            ->method('getDependsOnPropertyCollection');

        $oldProperty = $this->createMock(OldProperty::class);
        $oldProperty
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('oldPropertyLabel');
        $oldProperty
            ->expects($this->never())
            ->method('getPropertyType');
        $oldProperty
            ->expects($this->never())
            ->method('getRangeUri');
        $oldProperty
            ->expects($this->never())
            ->method('getValidationRules');
        $oldProperty
            ->expects($this->never())
            ->method('getDependsOnPropertyCollection');

        $this->assertTrue($this->sut->isPropertyChanged($property, $oldProperty));
    }

    public function testTriggerIfHasChangesOnPropertyType(): void
    {
        $newPropertyType = $this->createMock(core_kernel_classes_Resource::class);
        $newPropertyType
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('newPropertyTypeUri');

        $oldPropertyType = $this->createMock(core_kernel_classes_Resource::class);
        $oldPropertyType
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('oldPropertyTypeUri');

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('propertyLabel');
        $property
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));
        $property
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn($newPropertyType);
        $property
            ->expects($this->never())
            ->method('getRange');
        $property
            ->expects($this->never())
            ->method('getPropertyValues');
        $property
            ->expects($this->never())
            ->method('getDependsOnPropertyCollection');

        $oldProperty = $this->createMock(OldProperty::class);
        $oldProperty
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('propertyLabel');
        $oldProperty
            ->expects($this->once())
            ->method('getPropertyType')
            ->willReturn($oldPropertyType);
        $oldProperty
            ->expects($this->never())
            ->method('getRangeUri');
        $oldProperty
            ->expects($this->never())
            ->method('getValidationRules');
        $oldProperty
            ->expects($this->never())
            ->method('getDependsOnPropertyCollection');

        $this->assertTrue($this->sut->isPropertyChanged($property, $oldProperty));
    }

    public function testIsRangeChangedIsTrue(): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $range = $this->createMock(core_kernel_classes_Resource::class);
        $oldProperty = $this->createMock(OldProperty::class);

        $range->method('getUri')
            ->willReturn('uri1');

        $property->method('getRange')
            ->willReturn($range);

        $oldProperty->method('getRangeUri')
            ->willReturn('uri2');

        $this->assertTrue(
            $this->sut->isRangeChanged(
                $property,
                $oldProperty
            )
        );
    }

    public function testIsRangeChangedIsFalse(): void
    {
        $this->assertFalse(
            $this->sut->isRangeChanged(
                $this->createMock(core_kernel_classes_Property::class),
                $this->createMock(OldProperty::class)
            )
        );
    }

    /**
     * @dataProvider isValidationRulesChangedDataProvider
     */
    public function testIsValidationRulesChanged(
        array $validationRules,
        array $oldValidationRules,
        bool $expected
    ): void {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));
        $property
            ->method('getPropertyValues')
            ->willReturn($validationRules);

        $oldProperty = $this->createMock(OldProperty::class);
        $oldProperty
            ->method('getValidationRules')
            ->willReturn($oldValidationRules);

        $this->assertEquals($expected, $this->sut->isValidationRulesChanged($property, $oldProperty));
    }

    /**
     * @dataProvider isDependsOnPropertyCollectionChangedDataProvider
     */
    public function testIsDependsOnPropertyCollectionChanged(bool $isEqual, bool $expected): void
    {
        $dependsOnPropertyCollection = $this->createMock(DependsOnPropertyCollection::class);
        $dependsOnPropertyCollection
            ->method('isEqual')
            ->willReturn($isEqual);
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->method('getDependsOnPropertyCollection')
            ->willReturn($dependsOnPropertyCollection);

        $oldDependsOnPropertyCollection = $this->createMock(DependsOnPropertyCollection::class);
        $oldProperty = $this->createMock(OldProperty::class);
        $oldProperty
            ->method('getDependsOnPropertyCollection')
            ->willReturn($oldDependsOnPropertyCollection);

        $this->assertEquals(
            $expected,
            $this->sut->isDependsOnPropertyCollectionChanged($property, $oldProperty)
        );
    }

    public function isValidationRulesChangedDataProvider(): array
    {
        return [
            [
                'validationRules' => [],
                'oldValidationRules' => [],
                'expected' => false,
            ],
            [
                'validationRules' => ['validationRule'],
                'oldValidationRules' => [],
                'expected' => true,
            ],
            [
                'validationRules' => ['validationRule'],
                'oldValidationRules' => ['validationRule'],
                'expected' => false,
            ],
        ];
    }

    public function isDependsOnPropertyCollectionChangedDataProvider(): array
    {
        return [
            [
                'isEqual' => false,
                'expected' => true,
            ],
            [
                'isEqual' => true,
                'expected' => false,
            ],
            [
                'isEqual' => false,
                'expected' => true,
            ],
        ];
    }
}
