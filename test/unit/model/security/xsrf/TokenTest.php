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
 * Copyright (c) 2017-2023 (original work) Open Assessment Technologies SA ;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\security\xsrf;

use oat\tao\model\security\xsrf\Token;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for oat\tao\model\security\Token
 */
class TokenTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $key = 'a token key';
        $timeStamp = 1234567890.123456;

        $data = [
            Token::TOKEN_KEY => $key,
            Token::TIMESTAMP_KEY => $timeStamp,
        ];

        $subject = new Token($data);
        $encodedTokenData = json_decode(json_encode($subject), true);

        // Applying rounding on float because of the limited precision.
        // See: https://www.php.net/manual/en/language.types.float.php
        $this->assertEquals(round($timeStamp, 2), round($encodedTokenData[Token::TIMESTAMP_KEY], 2));
        $this->assertEquals($key, $encodedTokenData[Token::TOKEN_KEY]);
    }

    public function testCreateNewToken(): void
    {
        $token = new Token();

        $this->assertSame(40, strlen($token->getValue()), 'The token has the expected length');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{40}$/',
            $token->getValue(),
            'The token is correctly formatted'
        );
    }

    public function testIsExpired(): void
    {
        $token = new Token(
            [
                Token::TOKEN_KEY => 'abc123',
                Token::TIMESTAMP_KEY => microtime(true) - 2,
            ]
        );

        self::assertTrue($token->isExpired(1));
    }

    public function testIsNotExpired(): void
    {
        $token = new Token(
            [
                Token::TOKEN_KEY => 'abc123',
                Token::TIMESTAMP_KEY => microtime(true),
            ]
        );

        self::assertFalse($token->isExpired(5));
    }
}
