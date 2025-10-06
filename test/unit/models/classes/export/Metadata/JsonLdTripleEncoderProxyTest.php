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
use oat\generis\model\data\Ontology;
use PHPUnit\Framework\TestCase;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\model\export\Metadata\JsonLd\JsonLdTripleEncoderInterface;
use oat\tao\model\export\Metadata\JsonLd\JsonLdTripleEncoderProxy;
use PHPUnit\Framework\MockObject\MockObject;
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

class JsonLdTripleEncoderProxyTest extends TestCase
{
    /** @var Ontology|MockObject */
    private $ontology;

    /** @var JsonLdTripleEncoderInterface|MockObject */
    private $jsonLdTripleEncoder;

    /** @var JsonLdTripleEncoderProxy */
    private $sut;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->jsonLdTripleEncoder = $this->createMock(JsonLdTripleEncoderInterface::class);
        $this->sut = new JsonLdTripleEncoderProxy($this->ontology);
        $this->sut->addEncoder($this->jsonLdTripleEncoder);
    }

    public function testEncode(): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);

        $property->method('getAlias')
            ->willReturn('Property_Alias');

        $widget = $this->createMock(core_kernel_classes_Property::class);

        $widget->method('getUri')
            ->willReturn(tao_helpers_form_elements_Textbox::WIDGET_ID);

        $property
            ->method('getWidget')
            ->willReturn($widget);

        $this->ontology
            ->method('getProperty')
            ->willReturn($property);

        $this->jsonLdTripleEncoder
            ->method('isWidgetSupported')
            ->willReturn(true);

        $this->jsonLdTripleEncoder->method('encode')
            ->willReturnCallback(
                function (array $data) {
                    $data['new_field'] = 'new_value';

                    return $data;
                }
            );

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
                'new_field' => 'new_value',
            ],
            $this->sut->encode($data, $triple, $property, $widget)
        );
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
                true,
            ],
            [
                tao_helpers_form_elements_Treebox::WIDGET_ID,
                true,
            ],
            [
                tao_helpers_form_elements_Combobox::WIDGET_ID,
                true,
            ],
            [
                tao_helpers_form_elements_Checkbox::WIDGET_ID,
                true,
            ],
            [
                SearchTextBox::WIDGET_ID,
                true,
            ],
            [
                SearchDropdown::WIDGET_ID,
                true,
            ],
        ];
    }
}
