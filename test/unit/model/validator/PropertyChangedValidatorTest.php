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

use PHPUnit\Framework\TestCase;
use oat\tao\model\dto\OldProperty;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\validator\PropertyChangedValidator;
use oat\generis\model\resource\DependsOnPropertyCollection;

class PropertyChangedValidatorTest extends TestCase
{
    private const DEFAULT_RANGE_URI = 'rangeUri';
    private const DEFAULT_VALIDATION_RULES = [];

    /** @var PropertyChangedValidator  */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new PropertyChangedValidator();
    }

    public function testDoNotTriggerIfDoesNotHaveChanges(): void
    {
        $propertyType = $this->createPropertyTypeMock(2);
        $dependsOnPropertyCollection = $this->createDependsOnPropertyCollectionMock(true);

        $property = $this->createPropertyMock(
            'propertyLabel',
            $propertyType,
            $this->createRangeMock(),
            $dependsOnPropertyCollection,
            [
                'getLabel' => 1,
                'getProperty' => 2,
                'getOnePropertyValue' => 1,
                'getRange' => 1,
                'getPropertyValues' => 1,
                'getDependsOnPropertyCollection' => 1,
            ]
        );

        $oldProperty = $this->createOldPropertyMock(
            'propertyLabel',
            $propertyType,
            $dependsOnPropertyCollection,
            [
                'getLabel' => 1,
                'getPropertyType' => 1,
                'getRangeUri' => 1,
                'getValidationRules' => 1,
                'getDependsOnPropertyCollection' => 1,
            ]
        );

        $this->assertFalse($this->sut->isPropertyChanged($property, $oldProperty));
    }

    public function testDoNotTriggerIfDoesNotHavePropertyType(): void
    {
        $dependsOnPropertyCollection = $this->createDependsOnPropertyCollectionMock(true);

        $property = $this->createPropertyMock(
            'propertyLabel',
            null,
            $this->createRangeMock(),
            $dependsOnPropertyCollection,
            [
                'getLabel' => 1,
                'getProperty' => 2,
                'getOnePropertyValue' => 1,
                'getRange' => 1,
                'getPropertyValues' => 1,
                'getDependsOnPropertyCollection' => 1,
            ]
        );

        $oldProperty = $this->createOldPropertyMock(
            'propertyLabel',
            null,
            $dependsOnPropertyCollection,
            [
                'getLabel' => 1,
                'getPropertyType' => 1,
                'getRangeUri' => 1,
                'getValidationRules' => 1,
                'getDependsOnPropertyCollection' => 1,
            ]
        );

        $this->assertFalse($this->sut->isPropertyChanged($property, $oldProperty));
    }

    public function testTriggerIfHaveCurrentPropertyTypeButDoesNotHaveOldPropertyType(): void
    {
        $property = $this->createPropertyMock(
            'propertyLabel',
            $this->createPropertyTypeMock(),
            null,
            null,
            [
                'getLabel' => 1,
                'getProperty' => 1,
                'getOnePropertyValue' => 1,
            ]
        );

        $oldProperty = $this->createOldPropertyMock(
            'propertyLabel',
            null,
            null,
            [
                'getLabel' => 1,
                'getPropertyType' => 1,
            ]
        );

        $this->assertTrue($this->sut->isPropertyChanged($property, $oldProperty));
    }

    public function testTriggerIfDoesNotHaveCurrentPropertyTypeButHaveOldPropertyType(): void
    {
        $property = $this->createPropertyMock(
            'propertyLabel',
            null,
            null,
            null,
            [
                'getLabel' => 1,
                'getProperty' => 1,
                'getOnePropertyValue' => 1,
            ]
        );

        $oldProperty = $this->createOldPropertyMock(
            'propertyLabel',
            $this->createPropertyTypeMock(),
            null,
            [
                'getLabel' => 1,
                'getPropertyType' => 1,
            ]
        );

        $this->assertTrue($this->sut->isPropertyChanged($property, $oldProperty));
    }

    public function testTriggerIfHasChangesOnLabel(): void
    {
        $property = $this->createPropertyMock(
            'newPropertyLabel',
            null,
            null,
            null,
            [
                'getLabel' => 1,
            ]
        );

        $oldProperty = $this->createOldPropertyMock(
            'propertyLabel',
            null,
            null,
            [
                'getLabel' => 1,
            ]
        );

        $this->assertTrue($this->sut->isPropertyChanged($property, $oldProperty));
    }

    public function testTriggerIfHasChangesOnPropertyType(): void
    {
        $property = $this->createPropertyMock(
            'propertyLabel',
            $this->createPropertyTypeMock(1, 'newPropertyTypeUri'),
            null,
            null,
            [
                'getLabel' => 1,
                'getProperty' => 1,
                'getOnePropertyValue' => 1,
            ]
        );

        $oldProperty = $this->createOldPropertyMock(
            'propertyLabel',
            $this->createPropertyTypeMock(1, 'oldPropertyTypeUri'),
            null,
            [
                'getLabel' => 1,
                'getPropertyType' => 1,
            ]
        );

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
        $dependsOnPropertyCollection = $this->createDependsOnPropertyCollectionMock($isEqual);
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->method('getDependsOnPropertyCollection')
            ->willReturn($dependsOnPropertyCollection);

        $oldProperty = $this->createMock(OldProperty::class);
        $oldProperty
            ->method('getDependsOnPropertyCollection')
            ->willReturn($this->createMock(DependsOnPropertyCollection::class));

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

    private function createPropertyTypeMock(
        int $expects = 0,
        string $uri = 'propertyTypeUri'
    ): core_kernel_classes_Resource {
        $propertyType = $this->createMock(core_kernel_classes_Resource::class);

        if ($expects === 0) {
            $propertyType
                ->expects($this->never())
                ->method('getUri');
        } else {
            $propertyType
                ->expects($this->exactly($expects))
                ->method('getUri')
                ->willReturn($uri);
        }

        return $propertyType;
    }

    private function createRangeMock(): core_kernel_classes_Class
    {
        $range = $this->createMock(core_kernel_classes_Class::class);
        $range
            ->expects($this->once())
            ->method('getUri')
            ->willReturn(self::DEFAULT_RANGE_URI);

        return $range;
    }

    private function createDependsOnPropertyCollectionMock(bool $isEqual): DependsOnPropertyCollection
    {
        $dependsOnPropertyCollection = $this->createMock(DependsOnPropertyCollection::class);
        $dependsOnPropertyCollection
            ->expects($this->once())
            ->method('isEqual')
            ->willReturn($isEqual);

        return $dependsOnPropertyCollection;
    }

    private function createPropertyMock(
        string $propertyLabel,
        ?core_kernel_classes_Resource $propertyType,
        ?core_kernel_classes_Class $range,
        ?DependsOnPropertyCollection $dependsOnPropertyCollection,
        array $expects = []
    ): core_kernel_classes_Property {
        $property = $this->createMock(core_kernel_classes_Property::class);

        $this
            ->configureMethod(
                $property,
                'getLabel',
                $expects['getLabel'] ?? 0,
                $propertyLabel
            )
            ->configureMethod(
                $property,
                'getProperty',
                $expects['getProperty'] ?? 0,
                $this->createMock(core_kernel_classes_Property::class)
            )
            ->configureMethod(
                $property,
                'getOnePropertyValue',
                $expects['getOnePropertyValue'] ?? 0,
                $propertyType
            )
            ->configureMethod(
                $property,
                'getRange',
                $expects['getRange'] ?? 0,
                $range
            )
            ->configureMethod(
                $property,
                'getPropertyValues',
                $expects['getPropertyValues'] ?? 0,
                self::DEFAULT_VALIDATION_RULES
            )
            ->configureMethod(
                $property,
                'getDependsOnPropertyCollection',
                $expects['getDependsOnPropertyCollection'] ?? 0,
                $dependsOnPropertyCollection
            );

        return $property;
    }

    private function createOldPropertyMock(
        string $propertyLabel,
        ?core_kernel_classes_Resource $propertyType,
        ?DependsOnPropertyCollection $dependsOnPropertyCollection,
        array $expects = []
    ): OldProperty {
        $oldProperty = $this->createMock(OldProperty::class);

        $this
            ->configureMethod(
                $oldProperty,
                'getLabel',
                $expects['getLabel'] ?? 0,
                $propertyLabel
            )
            ->configureMethod(
                $oldProperty,
                'getPropertyType',
                $expects['getPropertyType'] ?? 0,
                $propertyType
            )
            ->configureMethod(
                $oldProperty,
                'getRangeUri',
                $expects['getRangeUri'] ?? 0,
                self::DEFAULT_RANGE_URI
            )
            ->configureMethod(
                $oldProperty,
                'getValidationRules',
                $expects['getValidationRules'] ?? 0,
                self::DEFAULT_VALIDATION_RULES
            )
            ->configureMethod(
                $oldProperty,
                'getDependsOnPropertyCollection',
                $expects['getDependsOnPropertyCollection'] ?? 0,
                $dependsOnPropertyCollection
            );

        return $oldProperty;
    }

    private function configureMethod(MockObject $mockObject, string $method, int $expects, $returnValue): self
    {
        if ($expects === 0) {
            $mockObject
                ->expects($this->never())
                ->method($method);

            return $this;
        }

        $mockObject
            ->expects($this->exactly($expects))
            ->method($method)
            ->willReturn($returnValue);

        return $this;
    }
}
