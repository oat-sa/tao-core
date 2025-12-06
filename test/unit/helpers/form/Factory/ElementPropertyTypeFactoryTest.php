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

use core_kernel_classes_Property;
use PHPUnit\Framework\TestCase;
use oat\tao\helpers\form\Factory\ElementFactoryContext;
use oat\tao\helpers\form\Factory\ElementPropertyTypeFactory;
use oat\tao\helpers\form\Specification\DependencyPropertyWidgetSpecification;
use oat\tao\model\Context\ContextInterface;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;
use oat\tao\model\Specification\PropertySpecificationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use tao_helpers_form_elements_xhtml_Combobox;

class ElementPropertyTypeFactoryTest extends TestCase
{
    /** @var ElementPropertyTypeFactory */
    private $sut;

    /** @var PropertySpecificationInterface */
    private $primaryPropertySpecification;

    /** @var SecondaryPropertySpecification */
    private $secondaryPropertySpecification;

    /** @var FeatureFlagCheckerInterface */
    private $featureFlagChecker;

    /** @var MockObject|tao_helpers_form_elements_xhtml_Combobox */
    private $element;

    /** @var array */
    private $propertyMap;

    /** @var DependencyPropertyWidgetSpecification|MockObject */
    private $dependencyPropertyWidgetSpecification;

    protected function setUp(): void
    {
        $this->primaryPropertySpecification = $this->createMock(PropertySpecificationInterface::class);
        $this->secondaryPropertySpecification = $this->createMock(SecondaryPropertySpecification::class);
        $this->dependencyPropertyWidgetSpecification = $this->createMock(DependencyPropertyWidgetSpecification::class);
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);
        $this->element = $this->getMockBuilder(tao_helpers_form_elements_xhtml_Combobox::class)
            ->setMethodsExcept(
                [
                    'setEmptyOption',
                    'setOptions',
                    'getOptions',
                    'setValue',
                    'getRawValue',
                    'addAttribute',
                    'getAttributes',
                ]
            )->getMock();
        $this->propertyMap = [
            'longlist' => [
                'title' => 'Combobox Title',
                'widget' => tao_helpers_form_elements_xhtml_Combobox::WIDGET_ID,
            ],
        ];

        $this->sut = new ElementPropertyTypeFactory(
            $this->primaryPropertySpecification,
            $this->secondaryPropertySpecification,
            $this->dependencyPropertyWidgetSpecification,
            $this->featureFlagChecker
        );
        $this->sut->withElement($this->element);
        $this->sut->withPropertyMap($this->propertyMap);
    }

    public function testCreate(): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $context = $this->createMock(ContextInterface::class);
        $context->method('getParameter')
            ->willReturnCallback(
                function ($param) use ($property) {
                    if ($param === ElementFactoryContext::PARAM_INDEX) {
                        return 1;
                    }

                    if ($param === ElementFactoryContext::PARAM_PROPERTY) {
                        return $property;
                    }

                    if ($param === ElementFactoryContext::PARAM_DATA) {
                        return [
                            '1_type' => 'longlist',
                        ];
                    }
                }
            );

        $this->featureFlagChecker
            ->method('isEnabled')
            ->willReturn(true);

        $this->primaryPropertySpecification
            ->method('isSatisfiedBy')
            ->with($property)
            ->willReturn(true);

        $this->secondaryPropertySpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->dependencyPropertyWidgetSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $element = $this->sut->create($context);

        $this->assertSame(
            'longlist',
            $element->getRawValue()
        );
        $this->assertSame(
            [
                'longlist' => 'Combobox Title'
            ],
            $element->getOptions()
        );
        $this->assertSame(
            tao_helpers_form_elements_xhtml_Combobox::WIDGET_ID,
            $element->getAttributes()[ElementPropertyTypeFactory::PROPERTY_TYPE_ATTRIBUTE] ?? null
        );
    }
}
