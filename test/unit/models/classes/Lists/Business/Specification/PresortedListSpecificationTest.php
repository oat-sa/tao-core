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

use core_kernel_classes_Class;
use core_kernel_classes_ContainerCollection;
use core_kernel_classes_Property;
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Specification\PresortedListSpecification;
use oat\tao\test\Asset\CustomRootClassFixture;
use tao_helpers_form_elements_Calendar;
use tao_helpers_form_elements_Checkbox;
use tao_helpers_form_elements_Combobox;
use tao_helpers_form_elements_Radiobox;

class PresortedListSpecificationTest extends TestCase
{
    /**
     * @dataProvider isSatisfiedByDataProvider
     */
    public function testIsSatisfiedBy(
        bool $expected,
        core_kernel_classes_Property $property,
        core_kernel_classes_Class $rootListClass = null
    ): void {
        $sut = new PresortedListSpecification($rootListClass);

        $this->assertEquals($expected, $sut->isSatisfiedBy($property));
    }

    public function isSatisfiedByDataProvider(): array
    {
        return [
            'Radiobox properties with non-list ranges are not presorted lists' => [
                'expected' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Radiobox::WIDGET_ID,
                    false
                ),
                'rootListClass' => null,
            ],
            'Radiobox properties with list ranges are presorted lists' => [
                'expected' => true,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Radiobox::WIDGET_ID,
                    true
                ),
                'rootListClass' => null,
            ],
            'Combobox properties with non-list ranges are not presorted lists' => [
                'expected' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Combobox::WIDGET_ID,
                    false
                ),
                'rootListClass' => null,
            ],
            'Combobox properties with list ranges are presorted lists' => [
                'expected' => true,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Combobox::WIDGET_ID,
                    true
                )
            ],
            'Checkbox properties with non-list ranges are not presorted lists' => [
                'expected' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Checkbox::WIDGET_ID,
                    false
                )
            ],
            'Checkbox properties with list ranges are presorted lists' => [
                'expected' => true,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Checkbox::WIDGET_ID,
                    true
                )
            ],
            'Calendar properties with list ranges are not lists' => [
                'expected' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Calendar::WIDGET_ID,
                    true
                )
            ],
            'Lists with non-class ranges are not presorted lists' => [
                'expected' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Radiobox::WIDGET_ID,
                    false,
                    false
                )
            ],
            'Lists with a non-class used as list range don\'t break' => [
                'expected' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Radiobox::WIDGET_ID,
                    false,
                    false,
                    \core_kernel_classes_ContainerCollection::class
                )
            ],
            'Lists with no widget information are not presorted lists' => [
                'expected' => false,
                'property' => $this->getMockProperty(
                    null,
                    true
                )
            ],
            'Lists matching the custom root class are presorted lists' => [
                'expected' => true,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Radiobox::WIDGET_ID,
                    true,
                    true,
                    CustomRootClassFixture::class
                ),
                'rootListClass' => new CustomRootClassFixture()
            ],
            'Lists not matching the custom root class are not presorted lists' => [
                'expected' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Radiobox::WIDGET_ID,
                    true,
                    true,
                    core_kernel_classes_ContainerCollection::class
                ),
                'rootListClass' => new CustomRootClassFixture(),
            ],
        ];
    }

    private function getMockProperty(
        ?string $widgetType,
        bool $isList,
        bool $isClass = true,
        string $rangeObjectType = core_kernel_classes_Class::class
    ): core_kernel_classes_Property {
        $rangeClass = $this->createMock($rangeObjectType);

        if (is_a($rangeClass, \core_kernel_classes_Resource::class)) {
            $rangeClass
                ->method('isClass')
                ->willReturn($isClass);
        }

        if (is_a($rangeClass, \core_kernel_classes_Class::class)) {
            $rangeClass
                ->method('isSubClassOf')
                ->willReturn($isList);
        }

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->method('getRange')
            ->withAnyParameters()
            ->willReturn($rangeClass);

        if ($widgetType) {
            $widgetMock = $this->createMock(core_kernel_classes_Property::class);
            $widgetMock
                ->method('getUri')
                ->willReturn($widgetType);
        }

        $property
            ->method('getWidget')
            ->willReturn($widgetType ? $widgetMock : null);

        return $property;
    }
}
