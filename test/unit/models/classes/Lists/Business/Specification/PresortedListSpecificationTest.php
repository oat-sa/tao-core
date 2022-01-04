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

namespace oat\tao\test\unit\model\Lists\Business\Specification;

use core_kernel_classes_Property;
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Specification\PresortedListSpecification;
use tao_helpers_form_elements_Calendar;
use tao_helpers_form_elements_Checkbox;
use tao_helpers_form_elements_Combobox;
use tao_helpers_form_elements_Radiobox;
use core_kernel_classes_Class;

class PresortedListSpecificationTest extends TestCase
{
    /**
     * @dataProvider isSatisfiedByDataProvider
     */
    public function testIsSatisfiedBy(
        bool $expected,
        PresortedListSpecification $sut,
        core_kernel_classes_Property $property
    ): void
    {
        $this->assertEquals($expected, $sut->isSatisfiedBy($property));
    }

    public function isSatisfiedByDataProvider(): array
    {
        return [
            'Radiobox properties with non-list ranges are not presorted lists' => [
                'expected' => false,
                'sut' => new PresortedListSpecification(),
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Radiobox::WIDGET_ID,
                    false
                )
            ],
            'Radiobox properties with list ranges are presorted lists' => [
                'expected' => true,
                'sut' => new PresortedListSpecification(),
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Radiobox::WIDGET_ID,
                    true
                )
            ],

            'Combobox properties with non-list ranges are not presorted lists' => [
                'expected' => false,
                'sut' => new PresortedListSpecification(),
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Combobox::WIDGET_ID,
                    false
                )
            ],
            'Combobox properties with list ranges are presorted lists' => [
                'expected' => true,
                'sut' => new PresortedListSpecification(),
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Combobox::WIDGET_ID,
                    true
                )
            ],

            'Checkbox properties with non-list ranges are not presorted lists' => [
                'expected' => false,
                'sut' => new PresortedListSpecification(),
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Checkbox::WIDGET_ID,
                    false
                )
            ],
            'Checkbox properties with list ranges are presorted lists' => [
                'expected' => true,
                'sut' => new PresortedListSpecification(),
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Checkbox::WIDGET_ID,
                    true
                )
            ],

            'Calendar properties with list ranges are not lists' => [
                'expected' => false,
                'sut' => new PresortedListSpecification(),
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Calendar::WIDGET_ID,
                    true
                )
            ],
            'Lists with non-class ranges are not presorted lists' => [
                'expected' => false,
                'sut' => new PresortedListSpecification(),
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Radiobox::WIDGET_ID,
                    false,
                    false
                )
            ],
        ];
    }

    private function getMockProperty(
        string $widgetType,
        bool $isList,
        bool $isClass = true
    ): core_kernel_classes_Property
    {
        $rangeClass = $this->createMock(core_kernel_classes_Class::class);
        $rangeClass
            ->method('isClass')
            ->willReturn($isClass);

        $rangeClass
            ->method('isSubClassOf')
            ->willReturn($isList);

        $widgetMock = $this->createMock(core_kernel_classes_Property::class);
        $widgetMock
            ->method('getUri')
            ->willReturn($widgetType);

        /// ------------

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->method('getRange')
            ->withAnyParameters()
            ->willReturn($rangeClass);

        $property
            ->method('getWidget')
            ->willReturn($widgetMock);

        return $property;
    }
}
