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
use oat\tao\model\Lists\Business\Specification\PropertySpecificationContext;
use PHPUnit\Framework\MockObject\MockObject;

class PropertySpecificationContextTest extends TestCase
{
    /** @var PropertySpecificationContext */
    private $sut;

    /** @var core_kernel_classes_Property|MockObject */
    private $property;

    /** @var array */
    private $data;

    protected function setUp(): void
    {
        $this->property = $this->createMock(core_kernel_classes_Property::class);
        $this->data = [];

        $this->sut = new PropertySpecificationContext(
            [
                PropertySpecificationContext::PARAM_FORM_INDEX => 1,
                PropertySpecificationContext::PARAM_PROPERTY => $this->property,
                PropertySpecificationContext::PARAM_FORM_DATA => $this->data,
            ]
        );
    }

    public function testGetters(): void
    {
        $this->assertSame(1, $this->sut->getParameter(PropertySpecificationContext::PARAM_FORM_INDEX));
        $this->assertSame($this->property, $this->sut->getParameter(PropertySpecificationContext::PARAM_PROPERTY));
        $this->assertSame($this->data, $this->sut->getParameter(PropertySpecificationContext::PARAM_FORM_DATA));
    }
}
