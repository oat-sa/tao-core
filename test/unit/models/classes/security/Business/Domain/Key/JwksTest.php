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
use oat\tao\model\security\Business\Domain\Key\Jwk;
use oat\tao\model\security\Business\Domain\Key\Jwks;

class JwksTest extends TestCase
{
    /** @var Jwks */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Jwks(new Jwk('kty', 'e', 'n', 'kid', 'alg', 'use'));
    }

    public function testGetters(): void
    {
        $this->assertSame(
            [
                'keys' => [
                    [
                        'kty' => 'kty',
                        'e' => 'e',
                        'n' => 'n',
                        'kid' => 'kid',
                        'alg' => 'alg',
                        'use' => 'use',
                    ]
                ]
            ],
            json_decode(json_encode($this->subject->jsonSerialize()), true)
        );
    }
}
