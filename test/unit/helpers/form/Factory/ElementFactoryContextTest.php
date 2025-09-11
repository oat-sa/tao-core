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
use PHPUnit\Framework\MockObject\MockObject;

class ElementFactoryContextTest extends TestCase
{
    /** @var ElementFactoryContext */
    private $sut;

    /** @var core_kernel_classes_Property|MockObject */
    private $property;

    /** @var array */
    private $data;

    protected function setUp(): void
    {
        $this->property = $this->createMock(core_kernel_classes_Property::class);
        $this->data = [];

        $this->sut = new ElementFactoryContext(
            [
                ElementFactoryContext::PARAM_RANGE => null,
                ElementFactoryContext::PARAM_INDEX => 1,
                ElementFactoryContext::PARAM_PROPERTY => $this->property,
                ElementFactoryContext::PARAM_DATA => $this->data,
            ]
        );
    }

    public function testGetters(): void
    {
        $this->assertNull($this->sut->getParameter(ElementFactoryContext::PARAM_RANGE));
        $this->assertSame(1, $this->sut->getParameter(ElementFactoryContext::PARAM_INDEX));
        $this->assertSame($this->property, $this->sut->getParameter(ElementFactoryContext::PARAM_PROPERTY));
        $this->assertSame($this->data, $this->sut->getParameter(ElementFactoryContext::PARAM_DATA));
    }
}
