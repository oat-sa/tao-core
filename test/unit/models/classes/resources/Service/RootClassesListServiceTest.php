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

namespace oat\tao\test\unit\model\resources\Service;

use oat\tao\model\menu\Tree;
use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use oat\tao\model\menu\Section;
use oat\tao\model\menu\Perspective;
use oat\generis\model\data\Ontology;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\resources\Service\RootClassesListService;

class RootClassesListServiceTest extends TestCase
{
    /** @var RootClassesListService */
    private $sut;

    /** @var Ontology|MockObject */
    private $ontology;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->sut = new RootClassesListService($this->ontology, $this->createPerspectivesMock());
    }

    public function testList(): void
    {
        $class = $this->createMock(core_kernel_classes_Class::class);

        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($class);

        $this->assertEquals([$class], $this->sut->list());
    }

    public function testListUris(): void
    {
        $this->ontology
            ->expects($this->never())
            ->method('getClass');

        $this->assertEquals(['rootUri'], $this->sut->listUris());
    }

    /**
     * @return Perspective[]|MockObject[]
     */
    private function createPerspectivesMock(): array
    {
        $treeWithRootNode = $this->createMock(Tree::class);
        $treeWithRootNode
            ->expects($this->once())
            ->method('get')
            ->with('rootNode')
            ->willReturn('rootUri');

        $treeWithoutRootNode = $this->createMock(Tree::class);
        $treeWithoutRootNode
            ->expects($this->once())
            ->method('get')
            ->with('rootNode')
            ->willReturn(null);

        $section = $this->createMock(Section::class);
        $section
            ->expects($this->once())
            ->method('getTrees')
            ->willReturn([
                $treeWithRootNode,
                $treeWithoutRootNode,
            ]);

        $perspective = $this->createMock(Perspective::class);
        $perspective
            ->expects($this->once())
            ->method('getChildren')
            ->willReturn([$section]);

        return [$perspective];
    }
}
