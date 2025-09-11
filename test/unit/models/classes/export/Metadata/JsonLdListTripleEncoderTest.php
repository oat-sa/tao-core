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

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Triple;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\model\export\Metadata\JsonLd\JsonLdListTripleEncoder;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\Business\Specification\LocalListClassSpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListClassSpecification;
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

class JsonLdListTripleEncoderTest extends TestCase
{
    /** @var ValueCollectionService|MockObject */
    private $valueCollectionService;

    /** @var RemoteListClassSpecification|MockObject */
    private $remoteListClassSpecification;

    /** @var LocalListClassSpecification|MockObject */
    private $localListClassSpecification;

    /** @var JsonLdListTripleEncoder */
    private $sut;

    protected function setUp(): void
    {
        $this->valueCollectionService = $this->createMock(ValueCollectionService::class);
        $this->remoteListClassSpecification = $this->createMock(RemoteListClassSpecification::class);
        $this->localListClassSpecification = $this->createMock(LocalListClassSpecification::class);
        $this->sut = new JsonLdListTripleEncoder(
            $this->valueCollectionService,
            $this->remoteListClassSpecification,
            $this->localListClassSpecification
        );
    }

    public function testEncode(): void
    {
        $this->remoteListClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->localListClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $value = $this->createMock(Value::class);
        $value->method('getLabel')
            ->willReturn('label');

        $valueCollection = $this->createMock(ValueCollection::class);
        $valueCollection->method('extractValueByUri')
            ->willReturn($value);

        $this->valueCollectionService
            ->method('findAll')
            ->willReturn($valueCollection);

        $range = $this->createMock(core_kernel_classes_Class::class);

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property->method('getAlias')
            ->willReturn('Property_Alias');
        $property->method('getLabel')
            ->willReturn('Property_Label');
        $property->method('getRange')
            ->willReturn($range);

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
                    'alias' => 'Property_Alias',
                    'label' => 'Property_Label',
                    'type' => tao_helpers_form_elements_Textbox::WIDGET_ID,
                    'value' => [
                        [
                            'value' => 'triple_object',
                            'label' => 'label',
                        ],
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
            [
                tao_helpers_form_elements_Textbox::WIDGET_ID,
                false,
            ],
            [
                tao_helpers_form_elements_Textarea::WIDGET_ID,
                false,
            ],
            [
                tao_helpers_form_elements_Htmlarea::WIDGET_ID,
                false,
            ],
            [
                tao_helpers_form_elements_Calendar::WIDGET_ID,
                false,
            ],
            [
                tao_helpers_form_elements_Hiddenbox::WIDGET_ID,
                false,
            ],
        ];
    }
}
