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

namespace oat\tao\test\unit\model\search;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use PHPUnit\Framework\TestCase;
use oat\tao\model\search\tokenizer\ResourceClasses;

class ResourceClassesTest extends TestCase
{
    /** @var ResourceClasses */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ResourceClasses();
    }

    public function testGetStrings(): void
    {
        $type = $this->createMock(core_kernel_classes_Class::class);

        $parentClass = $this->createMock(core_kernel_classes_Class::class);
        $forbiddenParentParentClass = $this->createMock(core_kernel_classes_Class::class);

        $type->method('getLabel')
            ->willReturn('Type1');

        $type->method('getParentClasses')
            ->willReturn(
                [
                    $parentClass,
                    $forbiddenParentParentClass
                ]
            );

        $parentClass->method('getLabel')
            ->willReturn('ParentClass1');

        $parentClass->method('getUri')
            ->willReturn('someAllowedUri');

        $forbiddenParentParentClass->method('getLabel')
            ->willReturn('Forbidden');

        $forbiddenParentParentClass->method('getUri')
            ->willReturn('http://www.tao.lu/Ontologies/TAO.rdf#AssessmentContentObject');

        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $resource->method('getTypes')
            ->willReturn(
                [
                    $type,
                ]
            );

        $this->assertSame(
            [
                'Type1',
                'ParentClass1',
            ],
            $this->subject->getStrings($resource)
        );
    }
}
