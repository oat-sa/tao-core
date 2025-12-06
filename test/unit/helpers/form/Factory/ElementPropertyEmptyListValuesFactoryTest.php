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
use oat\tao\helpers\form\Factory\AbstractElementPropertyListValuesFactory;
use oat\tao\helpers\form\Factory\ElementFactoryContext;
use oat\tao\helpers\form\Factory\ElementPropertyEmptyListValuesFactory;
use oat\tao\model\Context\ContextInterface;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;
use oat\tao\model\Specification\PropertySpecificationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use tao_helpers_form_elements_xhtml_Combobox;

class ElementPropertyEmptyListValuesFactoryTest extends TestCase
{
    /** @var ElementPropertyEmptyListValuesFactory */
    private $sut;

    /** @var PropertySpecificationInterface */
    private $primaryPropertySpecification;

    /** @var SecondaryPropertySpecification */
    private $secondaryPropertySpecification;

    /** @var FeatureFlagCheckerInterface */
    private $featureFlagChecker;

    /** @var tao_helpers_form_elements_xhtml_Combobox|MockObject */
    private $element;

    protected function setUp(): void
    {
        $this->primaryPropertySpecification = $this->createMock(PropertySpecificationInterface::class);
        $this->secondaryPropertySpecification = $this->createMock(SecondaryPropertySpecification::class);
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);
        $this->element = $this->getMockBuilder(tao_helpers_form_elements_xhtml_Combobox::class)
            ->setMethodsExcept(
                [
                    'setOptions',
                    'getOptions',
                    'disable',
                    'addAttribute',
                    'getAttributes',
                ]
            )->getMock();

        $this->sut = new ElementPropertyEmptyListValuesFactory(
            $this->primaryPropertySpecification,
            $this->secondaryPropertySpecification,
            $this->featureFlagChecker
        );
        $this->sut->withElement($this->element);
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

        $element = $this->sut->create($context);

        $this->assertEmpty($element->getOptions());
        $this->assertSame('true', $element->getAttributes()['data-force-disabled'] ?? null);
        $this->assertSame('disabled', $element->getAttributes()['disabled'] ?? null);
        $this->assertNotNull($element->getAttributes()['data-disabled-message'] ?? null);
        $this->assertSame(
            true,
            $element->getAttributes()[AbstractElementPropertyListValuesFactory::PROPERTY_LIST_ATTRIBUTE] ?? null
        );
    }
}
