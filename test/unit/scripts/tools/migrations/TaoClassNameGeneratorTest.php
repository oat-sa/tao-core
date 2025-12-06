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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\scripts\tools\migrations;

use common_ext_Extension as Extension;
use PHPUnit\Framework\TestCase;
use oat\tao\scripts\tools\migrations\TaoClassNameGenerator;

class TaoClassNameGeneratorTest extends TestCase
{
    public function testGenerateClassName()
    {
        $extFoo = $this->getMockBuilder(Extension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extFoo->method('getId')
            ->willReturn('foo');
        $generatorFoo = new TaoClassNameGenerator($extFoo);
        $namespace = 'oat\\' . $extFoo->getId() . '\\migrations';
        $className = $generatorFoo->generateClassName($namespace);
        $this->assertEquals(0, strpos($className, $namespace));
        $postfixPosition = strlen($className) - strlen($extFoo->getId());
        $this->assertEquals(substr($className, $postfixPosition), $extFoo->getId());
    }
}
