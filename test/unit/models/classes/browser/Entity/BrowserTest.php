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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\browser\Entity;

use oat\tao\model\browser\Entity\Browser;
use PHPUnit\Framework\TestCase;

class BrowserTest extends TestCase
{
    public function testJsonSerializeIncludesNameAndVersion(): void
    {
        $browser = new Browser('kiosked', '1.8.2');

        $this->assertSame('kiosked', $browser->name);
        $this->assertSame('1.8.2', $browser->version);
        $this->assertSame(
            ['name' => 'kiosked', 'version' => '1.8.2'],
            $browser->jsonSerialize()
        );
    }

    public function testVersionDefaultsToNull(): void
    {
        $browser = new Browser('Chrome');

        $this->assertNull($browser->version);
        $this->assertSame(['name' => 'Chrome', 'version' => null], $browser->jsonSerialize());
    }
}
