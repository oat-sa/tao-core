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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\test\unit\model\security;

use PHPUnit\Framework\TestCase;
use oat\tao\model\security\TokenGenerator;

/**
 * Unit Test of oat\tao\model\security\TokenGenerator
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class TokenGeneratorTest extends TestCase
{
    use TokenGenerator;

    /**
     * Test the token generation
     */
    public function testGenerate()
    {
        $token = $this->generate(40);
        $this->assertEquals(40, strlen($token), 'The token has the expected length');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $token, 'The token is correctly formatted');

        $token = $this->generate(60);
        $this->assertEquals(60, strlen($token), 'The token has the expected length');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{60}$/', $token, 'The token is correctly formatted');
    }

    /**
     * Micro collisions tests
     */
    public function testCollide()
    {
        $tokens = [];
        for ($i = 0; $i < 10000; $i++) {
            $tokens[] = $this->generate();
        }
        //if 2 values are identical the distribution size will be lower than the number of tokens
        $distribution = array_count_values($tokens);
        $this->assertCount(count($tokens), $distribution, 'The tokens are uniques');
    }
}
