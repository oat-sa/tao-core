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

namespace oat\tao\helpers\test\unit\helpers\form\Factory;

use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use oat\tao\helpers\form\Factory\AbstractElementPropertyListValuesFactory;
use oat\tao\helpers\form\Factory\ElementFactoryContext;
use oat\tao\helpers\form\Factory\ElementPropertyListValuesFactory;
use oat\tao\model\Context\ContextInterface;
use oat\tao\model\Specification\ClassSpecificationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use tao_helpers_form_elements_xhtml_Combobox;
use tao_models_classes_ListService;

class ElementPropertyListValuesFactoryTest extends TestCase
{
    /** @var ElementPropertyListValuesFactory */
    private $sut;

    /** @var ClassSpecificationInterface|MockObject */
    private $remoteListClassSpecification;

    /** @var MockObject|tao_models_classes_ListService */
    private $listService;

    /** @var MockObject|tao_helpers_form_elements_xhtml_Combobox */
    private $element;

    protected function setUp(): void
    {
        $this->remoteListClassSpecification = $this->createMock(ClassSpecificationInterface::class);
        $this->listService = $this->createMock(tao_models_classes_ListService::class);
        $this->element = $this->getMockBuilder(tao_helpers_form_elements_xhtml_Combobox::class)
            ->setMethodsExcept(
                [
                    'setEmptyOption',
                    'setOptions',
                    'getOptions',
                    'setValue',
                    'getRawValue',
                    'disable',
                    'addAttribute',
                    'getAttributes',
                    'addOptionAttribute',
                    'getOptionAttributes',
                ]
            )->getMock();

        $this->sut = new ElementPropertyListValuesFactory(
            $this->remoteListClassSpecification,
            $this->listService
        );
        $this->sut->withElement($this->element);
    }

    public function testCreate(): void
    {
        $listClass = $this->createMock(core_kernel_classes_Class::class);
        $listClass->method('getLabel')
            ->willReturn('label');
        $listClass->method('getUri')
            ->willReturn('listUri');

        $context = $this->createMock(ContextInterface::class);
        $context->method('getParameter')
            ->willReturnCallback(
                function ($param) use ($listClass) {
                    if ($param === ElementFactoryContext::PARAM_INDEX) {
                        return 1;
                    }

                    if ($param === ElementFactoryContext::PARAM_RANGE) {
                        return $listClass;
                    }
                }
            );

        $this->listService
            ->method('getLists')
            ->willReturn(
                [
                    $listClass,
                ]
            );

        $this->remoteListClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $element = $this->sut->create($context);

        $this->assertSame(
            'listUri',
            $element->getRawValue()
        );
        $this->assertSame(
            [
                'listUri' => 'label'
            ],
            $element->getOptions()
        );
        $this->assertSame(
            'true',
            $element->getOptionAttributes('listUri')[ElementPropertyListValuesFactory::OPTION_REMOTE_LIST_ATTRIBUTE]
            ?? null
        );
        $this->assertSame(
            true,
            $element->getAttributes()[AbstractElementPropertyListValuesFactory::PROPERTY_LIST_ATTRIBUTE] ?? null
        );
    }
}
