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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\resources\ResourceService;

use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use oat\tao\model\resources\ResourceService;

class ResourceServiceTest extends TestCase
{
    public function testGetAllClasses(): void
    {
        $service = new ResourceService();

        $tree = $this->getClassTree(5);
        $classes = $service->getAllClasses($tree);

        $this->assertSame(5, $this->getNestingLevel($classes));
    }

    public function testGetAllClassesLimitedNesting(): void
    {
        $service = new ResourceService(
            [
                'class-nesting-level' => 2
            ]
        );

        $tree = $this->getClassTree(5);
        $classes = $service->getAllClasses($tree);

        $this->assertSame(2, $this->getNestingLevel($classes));
    }

    private function getNestingLevel(array $array, int $level = 0): int
    {
        if (!empty($array['children'][0])) {
            return $this->getNestingLevel($array['children'][0], $level + 1);
        }

        return $level;
    }

    /**
     * @param int $level
     *
     * @return core_kernel_classes_Class
     */
    private function getClassTree(int $level): core_kernel_classes_Class
    {
        $subClasses = [];

        ++$level; // +1 root level

        for ($i = 0; $i < $level; ++$i) {
            $class = $this->createMock(core_kernel_classes_Class::class);
            $class->method('getUri')->willReturn(sprintf('http://class_%s', $i));
            $class->method('getLabel')->willReturn(sprintf('class_%s', $i));

            $subClasses[] = $class;
        }


        for ($i = 0; $i < $level - 1; ++$i) {
            $subClasses[$i]->method('getSubClasses')->willReturnCallback(
                static function (bool $recursive) use ($subClasses, $i) {
                    return $recursive
                        ? array_slice($subClasses, $i + 1)
                        : [$subClasses[$i + 1]];
                }
            );
        }

        $subClasses[$i]->method('getSubClasses')->willReturn([]);

        return $subClasses[0];
    }
}
