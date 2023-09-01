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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\resources\Service;

use PHPUnit\Framework\TestCase;
use core_kernel_classes_Property;
use oat\tao\model\resources\Service\ClassMetadataMapper;

class ClassMetadataMapperTest extends TestCase
{
    /** @var ClassMetadataMapper */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new ClassMetadataMapper();
    }

    public function testMapper(): void
    {
        $originalProperty = $this->createMock(core_kernel_classes_Property::class);
        $originalProperty
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('originalPropertyUri');

        $clonedProperty = $this->createMock(core_kernel_classes_Property::class);
        $clonedProperty
            ->expects($this->exactly(4))
            ->method('getUri')
            ->willReturn('clonedPropertyUri');

        $this->sut->add($originalProperty, $clonedProperty);

        $this->assertEquals('originalPropertyUri', $this->sut->get($clonedProperty));
        $this->assertNull($this->sut->get($originalProperty));

        $this->sut->remove([$clonedProperty]);

        $this->assertNull($this->sut->get($clonedProperty));
    }
}
