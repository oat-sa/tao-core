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
 */

declare(strict_types=1);

use oat\generis\model\WidgetRdf;
use oat\tao\model\dto\OldProperty;
use oat\generis\test\TestCase;
use oat\tao\model\validator\PropertyChangedValidator;

class PropertyChangedValidatorTest extends TestCase
{
    public function testDoNotTriggerIfDoesNotHaveChanges(): void
    {
        $property = $this->createPropertyMock('');

        $this->assertFalse(
            (new PropertyChangedValidator())->isPropertyChanged($property, new OldProperty('', $property))
        );
    }

    public function testDoNotTriggerIfDoesNotHavePropertyType(): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property->expects($this->once())
            ->method('getOnePropertyValue')
            ->with(new core_kernel_classes_Property(WidgetRdf::PROPERTY_WIDGET))
            ->willReturn(null);

        $this->assertFalse(
            (new PropertyChangedValidator())->isPropertyChanged($property, new OldProperty('', null))
        );
    }

    public function testTriggerIfHaveCurrentPropertyTypeButDoesNotHaveOldPropertyType(): void
    {
        $property = $this->createPropertyMock('');

        $this->assertTrue(
            (new PropertyChangedValidator())->isPropertyChanged($property, new OldProperty('', null))
        );
    }

    public function testTriggerIfDoesNotHaveCurrentPropertyTypeButHaveOldPropertyType(): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);

        $this->assertTrue(
            (new PropertyChangedValidator())->isPropertyChanged(
                $property,
                new OldProperty('', $this->createMock(core_kernel_classes_Property::class))
            )
        );
    }

    public function testTriggerIfHasChangesOnLabel(): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);

        $this->assertTrue(
            (new PropertyChangedValidator())->isPropertyChanged(
                $property,
                new OldProperty('different', $property)
            )
        );
    }

    public function testTriggerIfHasChangesOnPropertyType(): void
    {
        $property = $this->createPropertyMock('TextArea');

        $widgetProperty = $this->createMock(core_kernel_classes_Property::class);
        $widgetProperty->expects($this->once())
            ->method('getUri')
            ->willReturn('TextBox');

        $this->assertTrue(
            (new PropertyChangedValidator())->isPropertyChanged(
                $property,
                new OldProperty('', $widgetProperty)
            )
        );
    }

    private function createPropertyMock(string $widgetPropertyId = null): core_kernel_classes_Property
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property->expects($this->any())->method('getUri')->willReturn('');
        $property->expects($this->once())
            ->method('getOnePropertyValue')
            ->with(new core_kernel_classes_Property(WidgetRdf::PROPERTY_WIDGET))
            ->willReturnCallback(
                function () use ($widgetPropertyId): core_kernel_classes_Property {
                    $widgetProperty = $this->createMock(core_kernel_classes_Property::class);
                    $widgetProperty->expects($this->any())
                        ->method('getUri')
                        ->willReturn($widgetPropertyId);

                    return $widgetProperty;
                }
            );
        return $property;
    }
}
