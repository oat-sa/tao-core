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
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\security\Business\Domain\Key;

use PHPUnit\Framework\TestCase;
use oat\tao\model\security\Business\Domain\Key\Key;

class KeyTest extends TestCase
{
    /** @var Key */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Key('123456', 'passphrase');
    }

    public function testGetters(): void
    {
        $this->assertSame('123456', $this->subject->getValue());
        $this->assertSame('passphrase', $this->subject->getPassphrase());
    }
}
