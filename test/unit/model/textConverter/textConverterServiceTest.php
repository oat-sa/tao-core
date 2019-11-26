<?php

declare(strict_types=1);

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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\test\unit\model\textConverter;

use oat\generis\test\TestCase;
use oat\tao\model\textConverter\TextConverterService;

class textConverterServiceTest extends TestCase
{
    public function testGet(): void
    {
        $textConverter = $this->getMockForAbstractClass(TextConverterService::class);
        $textConverter->expects($this->any())
            ->method('getTextRegistry')
            ->will($this->returnValue(['fixture' => 'value']));

        $this->assertSame('notFoundFixture', $textConverter->get('notFoundFixture'));
        $this->assertSame(['notAStringFixture'], $textConverter->get(['notAStringFixture']));
        $this->assertSame('value', $textConverter->get('fixture'));
    }

    public function testGetRegistry(): void
    {
        $textConverter = $this->getMockForAbstractClass(TextConverterService::class);
        $textConverter->expects($this->any())
            ->method('getTextRegistry')
            ->will($this->returnValue(['fixture' => 'value']));

        $this->assertSame(['fixture' => 'value'], $textConverter->getTextRegistry());
    }
}
