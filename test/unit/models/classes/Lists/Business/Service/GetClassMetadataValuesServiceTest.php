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
 * Copyright (c) 2021-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Lists\Business\Service;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Business\Domain\Metadata;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Service\GetClassMetadataValuesService;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use tao_helpers_form_elements_Radiobox;
use tao_helpers_form_elements_Textbox;

class GetClassMetadataValuesServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    private GetClassMetadataValuesService $subject;
    private core_kernel_classes_Class|MockObject $classMock;
    private core_kernel_classes_Property|MockObject $propertyMock;
    private core_kernel_classes_Resource|MockObject $resourceMock;
    private ValueCollectionService|MockObject $valueCollectionServiceMock;
    private Value|MockObject $valueMock;

    protected function setUp(): void
    {
        $this->subject = new GetClassMetadataValuesService();
        $this->valueCollectionServiceMock = $this->createMock(ValueCollectionService::class);
        $this->classMock = $this->createMock(core_kernel_classes_Class::class);
        $this->propertyMock = $this->createMock(core_kernel_classes_Property::class);
        $this->resourceMock = $this->createMock(core_kernel_classes_Resource::class);
        $this->valueMock = $this->createMock(Value::class);

        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    ValueCollectionService::SERVICE_ID => $this->valueCollectionServiceMock
                ]
            )
        );
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGetByClassRecursive($widgetUri, $getRangeCount, $type, $getValues)
    {
        $this->classMock
            ->method('getProperties')
            ->with(true)
            ->willReturn(
                [
                    $this->propertyMock
                ]
            );

        $this->propertyMock
            ->method('getWidget')
            ->willReturn($this->resourceMock);

        $this->propertyMock
            ->method('getLabel')
            ->willReturn('Property Label Example');

        $this->propertyMock
            ->expects($this->exactly($getRangeCount))
            ->method('getRange')
            ->willReturn($this->resourceMock);

        $this->resourceMock
            ->method('getUri')
            ->willReturn($widgetUri);

        $this->valueCollectionServiceMock
            ->method('count')
            ->willReturn(2);

        $this->valueCollectionServiceMock
            ->method('findAll')
            ->willReturn(
                new ValueCollection('valueUri', $this->valueMock)
            );

        $this->valueMock
            ->method('getLabel')
            ->willReturn('Value Label');

        $result = $this->subject->getByClassRecursive(
            $this->classMock
        );

        $this->assertEquals(1, $result->count());
        $serialisedResult = $result->jsonSerialize();
        $resultElement = reset($serialisedResult);
        $this->assertInstanceOf(Metadata::class, $resultElement);
        $this->assertEquals('Property Label Example', $resultElement->getLabel());
        $this->assertEquals($type, $resultElement->getType());
        $this->assertNull($resultElement->getUri());
        $this->assertEquals($getValues, $resultElement->getValues());
    }

    public function getDataProvider()
    {
        return [
            'Textbox' => [
                tao_helpers_form_elements_Textbox::WIDGET_ID,
                0,
                'text',
                null,
            ],
            'RadioBox' => [
                tao_helpers_form_elements_Radiobox::WIDGET_ID,
                1,
                'list',
                [
                    'Element 1'
                ]
            ]
        ];
    }
}
