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

namespace oat\tao\helpers\test\unit\helpers\form\Decorator;

use tao_helpers_form_Form;
use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use tao_helpers_form_FormElement;
use oat\generis\model\data\Ontology;
use oat\generis\test\IteratorMockTrait;
use tao_helpers_form_elements_Combobox;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\helpers\form\Decorator\ElementDecorator;
use oat\generis\model\resource\DependsOnPropertyCollection;

class ElementDecoratorTest extends TestCase
{
    use IteratorMockTrait;

    private const FORM_DATA = [
        '1_uri' => 'someUri',
        '1_range' => 'classUri',
    ];

    /** @var ElementDecorator */
    private $sut;

    /** @var MockObject|string */
    private $ontology;

    /** @var MockObject|tao_helpers_form_Form */
    private $form;

    /** @var MockObject|tao_helpers_form_FormElement */
    private $element;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->form = $this->createMock(tao_helpers_form_Form::class);
        $this->element = $this->createMock(tao_helpers_form_FormElement::class);

        $this->sut = new ElementDecorator(
            $this->ontology,
            $this->form,
            $this->element
        );
    }

    public function testGetters(): void
    {
        $widget = $this->createMock(core_kernel_classes_Resource::class);
        $widget
            ->method('getUri')
            ->willReturn(tao_helpers_form_elements_Combobox::WIDGET_ID);

        $primaryProperty = $this->createMock(core_kernel_classes_Property::class);
        $primaryProperty
            ->method('getUri')
            ->willReturn('primaryPropertyUri');

        $dependsOnPropertyCollection = $this->createIteratorMock(
            DependsOnPropertyCollection::class,
            [$primaryProperty]
        );

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->method('exists')
            ->willReturn(true);
        $property
            ->method('getWidget')
            ->willReturn($widget);
        $property
            ->method('getDependsOnPropertyCollection')
            ->willReturn($dependsOnPropertyCollection);

        $primaryElement = $this->createMock(tao_helpers_form_FormElement::class);

        $this->form
            ->method('getValues')
            ->willReturn(self::FORM_DATA);
        $this->form
            ->method('getElement')
            ->with('primaryPropertyUri')
            ->willReturn($primaryElement);

        $this->element
            ->method('getName')
            ->willReturn('1_name');
        $this->element
            ->method('getInputValue')
            ->willReturn('https://test.test/inputValues#identifier');
        $this->element
            ->method('getRawValue')
            ->willReturn('longlist');

        $class = $this->createMock(core_kernel_classes_Class::class);

        $this->ontology
            ->method('getProperty')
            ->with('someUri')
            ->willReturn($property);
        $this->ontology
            ->method('getClass')
            ->with('classUri')
            ->willReturn($class);

        $this->assertSame(self::FORM_DATA, $this->sut->getFormData());
        $this->assertSame(1, $this->sut->getIndex());
        $this->assertSame($property, $this->sut->getProperty());
        $this->assertSame($class, $this->sut->getRangeClass());
        $this->assertSame(tao_helpers_form_elements_Combobox::WIDGET_ID, $this->sut->getCurrentWidgetUri());
        $this->assertSame(tao_helpers_form_elements_Combobox::WIDGET_ID, $this->sut->getNewWidgetUri());
        $this->assertSame(
            ['https_2_test_0_test_1_inputValues_3_identifier' => 'https://test.test/inputValues#identifier'],
            $this->sut->getListValues()
        );
        $this->assertCount(1, $this->sut->getPrimaryElementsDecorators());
    }
}
