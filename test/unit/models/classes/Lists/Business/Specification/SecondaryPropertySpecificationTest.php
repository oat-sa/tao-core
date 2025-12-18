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

namespace oat\tao\test\unit\model\Lists\Business\Specification;

use core_kernel_classes_Property;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Context\ContextInterface;
use oat\tao\model\Lists\Business\Specification\DependentPropertySpecification;
use oat\tao\model\Lists\Business\Specification\PropertySpecificationContext;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;

class SecondaryPropertySpecificationTest extends TestCase
{
    /** @var SecondaryPropertySpecification */
    private $sut;

    /** @var DependentPropertySpecification */
    private $dependentPropertySpecification;

    protected function setUp(): void
    {
        $this->dependentPropertySpecification = $this->createMock(DependentPropertySpecification::class);

        $this->sut = new SecondaryPropertySpecification(
            $this->dependentPropertySpecification
        );
    }

    public function testIsSatisfiedBy(): void
    {
        $formData = [];
        $property = $this->createMock(core_kernel_classes_Property::class);
        $context = $this->createMock(ContextInterface::class);
        $context->method('getParameter')
            ->willReturnCallback(
                function ($param) use ($property, $formData) {
                    if ($param === PropertySpecificationContext::PARAM_FORM_INDEX) {
                        return 1;
                    }

                    if ($param === PropertySpecificationContext::PARAM_PROPERTY) {
                        return $property;
                    }

                    if ($param === PropertySpecificationContext::PARAM_FORM_DATA) {
                        return $formData;
                    }
                }
            );

        $this->dependentPropertySpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->assertTrue($this->sut->isSatisfiedBy($context));
    }

    public function testIsSatisfiedByUsingFormData(): void
    {
        $formData = [
            '1_depends-on-property' => 'something'
        ];
        $context = $this->createMock(ContextInterface::class);
        $context->method('getParameter')
            ->willReturnCallback(
                function ($param) use ($formData) {
                    if ($param === PropertySpecificationContext::PARAM_FORM_INDEX) {
                        return 1;
                    }

                    if ($param === PropertySpecificationContext::PARAM_FORM_DATA) {
                        return $formData;
                    }
                }
            );

        $this->assertTrue($this->sut->isSatisfiedBy($context));
    }
}
