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

namespace oat\tao\test\unit\model\Lists\Business\Service;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Domain\Metadata;
use oat\tao\model\Lists\Business\Service\GetClassMetadataValuesService;
use tao_helpers_form_elements_Textbox;

class GetClassMetadataValuesServiceTest extends TestCase
{
    /** @var GetClassMetadataValuesService */
    private $subject;

    /** @var core_kernel_classes_Class|MockObject */
    private $classMock;

    /** @var core_kernel_classes_Property|MockObject */
    private $propertyMock;
    /** @var core_kernel_classes_Resource|MockObject */
    private $resourceMock;

    public function setUp(): void
    {
        $this->subject = new GetClassMetadataValuesService();
        $this->classMock = $this->createMock(core_kernel_classes_Class::class);
        $this->propertyMock = $this->createMock(core_kernel_classes_Property::class);
        $this->resourceMock = $this->createMock(core_kernel_classes_Resource::class);
    }

    public function testGetByClass()
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

        $this->resourceMock
            ->method('getUri')
            ->willReturn(tao_helpers_form_elements_Textbox::WIDGET_ID);


        $result = $this->subject->getByClass(
            $this->classMock
        );

        $this->assertEquals(1, $result->count());
        $serialisedResult = $result->jsonSerialize();
        $resultElement = reset($serialisedResult);
        $this->assertInstanceOf(Metadata::class, $resultElement);
        $this->assertEquals('Property Label Example', $resultElement->getLabel());
        $this->assertEquals('text', $resultElement->getType());
        $this->assertNull($resultElement->getUri());
        $this->assertNull($resultElement->getValues());
    }
}
