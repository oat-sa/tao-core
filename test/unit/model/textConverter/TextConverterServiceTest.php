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
 * Copyright (c) 2016-2025 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\textConverter;

use oat\tao\model\textConverter\TextConverterService;
use PHPUnit\Framework\TestCase;

class TextConverterServiceTest extends TestCase
{
    public function testGet()
    {
        $textConverter = $this->getMockForAbstractClass(TextConverterService::class);
        $textConverter->expects($this->any())
            ->method('getTextRegistry')
            ->will($this->returnValue(['fixture' => 'value']));

        $this->assertEquals('notFoundFixture', $textConverter->get('notFoundFixture'));
        $this->assertEquals(['notAStringFixture'], $textConverter->get(['notAStringFixture']));
        $this->assertEquals('value', $textConverter->get('fixture'));
    }

    public function testGetRegistry()
    {
        $textConverter = $this->getMockForAbstractClass(TextConverterService::class);
        $textConverter->expects($this->any())
            ->method('getTextRegistry')
            ->will($this->returnValue(['fixture' => 'value']));

        $this->assertEquals(['fixture' => 'value'], $textConverter->getTextRegistry());
    }
}
