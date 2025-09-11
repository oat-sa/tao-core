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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\search\index;

use core_kernel_classes_Property;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\model\search\index\DocumentBuilder\PropertyIndexReferenceFactory;
use PHPUnit\Framework\TestCase;
use tao_helpers_form_elements_Calendar;
use tao_helpers_form_elements_Checkbox;
use tao_helpers_form_elements_Combobox;
use tao_helpers_form_elements_Htmlarea;
use tao_helpers_form_elements_Radiobox;
use tao_helpers_form_elements_Textarea;
use tao_helpers_form_elements_Textbox;

class PropertyIndexReferenceFactoryTest extends TestCase
{
    /** @var PropertyIndexReferenceFactory */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new PropertyIndexReferenceFactory();
    }

    /**
     * @dataProvider getCreateProvider
     */
    public function testCreate(string $expected, string $widgetUri): void
    {
        $propertyWidget = $this->createMock(core_kernel_classes_Property::class);
        $propertyType = $this->createMock(core_kernel_classes_Property::class);
        $property = $this->createMock(core_kernel_classes_Property::class);

        $property->expects($this->once())
            ->method('getProperty')
            ->willReturn($propertyWidget);

        $property->expects($this->once())
            ->method('getUri')
            ->willReturn('uri');

        $property->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn($propertyType);

        $propertyType->expects($this->once())
            ->method('getUri')
            ->willReturn($widgetUri);

        $this->assertSame($expected, $this->sut->create($property));
    }

    public function getCreateProvider(): array
    {
        return [
            [
                'TextBox_uri',
                tao_helpers_form_elements_Textbox::WIDGET_ID,
            ],
            [
                'TextArea_uri',
                tao_helpers_form_elements_Textarea::WIDGET_ID,
            ],
            [
                'HTMLArea_uri',
                tao_helpers_form_elements_Htmlarea::WIDGET_ID,
            ],
            [
                'CheckBox_uri',
                tao_helpers_form_elements_Checkbox::WIDGET_ID,
            ],
            [
                'ComboBox_uri',
                tao_helpers_form_elements_Combobox::WIDGET_ID,
            ],
            [
                'RadioBox_uri',
                tao_helpers_form_elements_Radiobox::WIDGET_ID,
            ],
            [
                'Calendar_uri',
                tao_helpers_form_elements_Calendar::WIDGET_ID,
            ],
            [
                'SearchTextBox_uri',
                SearchTextBox::WIDGET_ID,
            ],
            [
                'SearchDropdown_uri',
                SearchDropdown::WIDGET_ID,
            ],
        ];
    }

    /**
     * @dataProvider getCreateProviderRaw
     */
    public function testCreateRaw(?string $expected, string $widgetUri): void
    {
        $propertyWidget = $this->createMock(core_kernel_classes_Property::class);
        $propertyType = $this->createMock(core_kernel_classes_Property::class);
        $property = $this->createMock(core_kernel_classes_Property::class);

        $property->expects($this->once())
            ->method('getProperty')
            ->willReturn($propertyWidget);

        $property->expects($this->once())
            ->method('getUri')
            ->willReturn('uri');

        $property->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn($propertyType);

        $propertyType->expects($this->once())
            ->method('getUri')
            ->willReturn($widgetUri);

        $this->assertSame($expected, $this->sut->createRaw($property));
    }

    public function getCreateProviderRaw(): array
    {
        return [
            [
                null,
                tao_helpers_form_elements_Textbox::WIDGET_ID,
            ],
            [
                null,
                tao_helpers_form_elements_Textarea::WIDGET_ID,
            ],
            [
                'HTMLArea_uri_raw',
                tao_helpers_form_elements_Htmlarea::WIDGET_ID,
            ],
            [
                'CheckBox_uri_raw',
                tao_helpers_form_elements_Checkbox::WIDGET_ID,
            ],
            [
                'ComboBox_uri_raw',
                tao_helpers_form_elements_Combobox::WIDGET_ID,
            ],
            [
                'RadioBox_uri_raw',
                tao_helpers_form_elements_Radiobox::WIDGET_ID,
            ],
            [
                null,
                tao_helpers_form_elements_Calendar::WIDGET_ID,
            ],
            [
                'SearchTextBox_uri_raw',
                SearchTextBox::WIDGET_ID,
            ],
            [
                'SearchDropdown_uri_raw',
                SearchDropdown::WIDGET_ID,
            ],
        ];
    }
}
