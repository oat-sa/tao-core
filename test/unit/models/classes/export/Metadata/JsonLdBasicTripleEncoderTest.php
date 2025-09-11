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

namespace oat\tao\test\unit\model\export\Metadata;

use core_kernel_classes_Property;
use core_kernel_classes_Triple;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\model\export\Metadata\JsonLd\JsonLdBasicTripleEncoder;
use stdClass;
use tao_helpers_form_elements_Calendar;
use tao_helpers_form_elements_Checkbox;
use tao_helpers_form_elements_Combobox;
use tao_helpers_form_elements_Hiddenbox;
use tao_helpers_form_elements_Htmlarea;
use tao_helpers_form_elements_Radiobox;
use tao_helpers_form_elements_Textarea;
use tao_helpers_form_elements_Textbox;
use tao_helpers_form_elements_Treebox;

class JsonLdBasicTripleEncoderTest extends TestCase
{
    /** @var JsonLdBasicTripleEncoder */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new JsonLdBasicTripleEncoder();
    }

    public function testEncode(): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property->method('getAlias')
            ->willReturn('Property_Alias');
        $property->method('getLabel')
            ->willReturn('Property_Label');

        $widget = $this->createMock(core_kernel_classes_Property::class);
        $widget->method('getUri')
            ->willReturn(tao_helpers_form_elements_Textbox::WIDGET_ID);

        $triple = new core_kernel_classes_Triple();
        $triple->subject = 'triple_subject';
        $triple->predicate = 'triple_predicate';
        $triple->object = 'triple_object';

        $context1 = new stdClass();
        $context1->property_label = 'triple_predicate';

        $data = [
            '@context' => $context1,
        ];

        $context = new stdClass();
        $context->property_label = 'triple_predicate';

        $this->assertEquals(
            [
                '@context' => $context,
                'property_label' => [
                    'type' => tao_helpers_form_elements_Textbox::WIDGET_ID,
                    'alias' => 'Property_Alias',
                    'label' => 'Property_Label',
                    'value' => [
                        [
                            'label' => null,
                            'value' => 'triple_object'
                        ]
                    ],
                ],
            ],
            $this->sut->encode($data, $triple, $property, $widget)
        );
    }

    public function testEncodeWithMissingParametersWillThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The parameters $property and $widget are required');

        $this->sut->encode([], new core_kernel_classes_Triple());
    }

    /**
     * @dataProvider supportedWidgetDataProvider
     */
    public function testIsWidgetSupported(string $widgetUri, bool $expected): void
    {
        $this->assertSame($expected, $this->sut->isWidgetSupported($widgetUri));
    }

    public function supportedWidgetDataProvider(): array
    {
        return [
            [
                tao_helpers_form_elements_Textbox::WIDGET_ID,
                true,
            ],
            [
                tao_helpers_form_elements_Textarea::WIDGET_ID,
                true,
            ],
            [
                tao_helpers_form_elements_Htmlarea::WIDGET_ID,
                true,
            ],
            [
                tao_helpers_form_elements_Calendar::WIDGET_ID,
                true,
            ],
            [
                tao_helpers_form_elements_Hiddenbox::WIDGET_ID,
                true,
            ],
            [
                tao_helpers_form_elements_Radiobox::WIDGET_ID,
                false,
            ],
            [
                tao_helpers_form_elements_Treebox::WIDGET_ID,
                false,
            ],
            [
                tao_helpers_form_elements_Combobox::WIDGET_ID,
                false,
            ],
            [
                tao_helpers_form_elements_Checkbox::WIDGET_ID,
                false,
            ],
            [
                SearchTextBox::WIDGET_ID,
                false,
            ],
            [
                SearchDropdown::WIDGET_ID,
                false,
            ],
        ];
    }
}
