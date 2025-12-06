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
use oat\tao\model\security\Business\Domain\Key\KeyChain;

class KeyChainTest extends TestCase
{
    private const NAME = 'name';
    private const ID = 'id';

    /** @var KeyChain */
    private $subject;

    /** @var Key */
    private $publicKey;

    /** @var Key */
    private $privateKey;

    protected function setUp(): void
    {
        $this->privateKey = new Key('123456');
        $this->publicKey = new Key('654321');
        $this->subject = new KeyChain(self::ID, self::NAME, $this->publicKey, $this->privateKey);
    }

    public function testGetters(): void
    {
        $this->assertSame(self::NAME, $this->subject->getName());
        $this->assertSame(self::ID, $this->subject->getIdentifier());
        $this->assertSame($this->publicKey, $this->subject->getPublicKey());
        $this->assertSame($this->privateKey, $this->subject->getPrivateKey());
    }
}
