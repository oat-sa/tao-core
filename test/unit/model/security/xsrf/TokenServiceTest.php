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

namespace oat\tao\test\unit\model\security\xsrf;

use common_exception_Unauthorized as UnauthorizedException;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\service\exception\InvalidService;
use oat\tao\model\security\xsrf\Token;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\model\security\xsrf\TokenStore;

/**
 * Unit Test of oat\tao\model\security\xsrf\TokenService
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class TokenServiceTest extends TestCase
{
    /**
     * @var TokenService
     */
    private $subject;

    /**
     * @var TokenStore|MockObject
     */
    private $tokenStoreMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenStoreMock = $this->createMock(TokenStore::class);

        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'poolSize' => 10,
                'timeLimit' => 0,
                'validateTokens' => true
            ]
        );
    }

    public function testValidateToken(): void
    {
        $token = $this->createTokenToValidate();

        static::assertTrue($this->subject->validateToken($token->getValue()));
    }

    public function testValidateExpiredToken(): void
    {
        $subject = clone $this->subject;
        $subject->setOption(TokenService::TIME_LIMIT_OPT, 1);

        $token = $this->createTokenToValidate();

        $token->setCreatedAt(0);

        $this->expectException(UnauthorizedException::class);

        $subject->validateToken($token->getValue());
    }

    public function testValidateIncorrectToken(): void
    {
        $invalidTokenValue = 'foo';

        $this->tokenStoreMock
            ->method('getToken')
            ->with($invalidTokenValue)
            ->willReturn(null);

        $this->expectException(UnauthorizedException::class);

        $this->subject->validateToken($invalidTokenValue);
    }

    public function testInstantiateNoStore(): void
    {
        $this->expectException(InvalidService::class);
        $service = new TokenService();
        $service->checkToken('unusedString');
    }

    public function testInstantiateBadStore(): void
    {
        $this->expectException(InvalidService::class);
        $service = new TokenService([
            'store' => []
        ]);
        $service->checkToken('unusedString');
    }

    public function testCreateToken_WhenCalledTwice_ThenReturnsDifferentNewTokes(): void
    {
        $this->tokenStoreMock
            ->expects(self::exactly(2))
            ->method('getAll')
            ->willReturn([]);

        $this->tokenStoreMock
            ->expects(self::exactly(2))
            ->method('setToken');

        $token1 = $this->subject->createToken();
        $token2 = $this->subject->createToken();

        self::assertNotEquals($token1->getValue(), $token2->getValue(), 'Method must return new token on each call.');
    }

    public function testCheckToken_WhenValidTokenObject_ThenReturnTrue(): void
    {
        $tokenString = 'TOKEN_STRING';
        $tokenData = [
            'token' => $tokenString,
            'ts' => 12345,
        ];
        $token = new Token($tokenData);
        $this->tokenStoreMock
            ->method('getToken')
            ->with($tokenString)
            ->willReturn($token);

        self::assertTrue($this->subject->checkToken($token), 'Method must return TRUE for valid token object.');
    }

    public function testCheckToken_WhenValidTokenString_ThenReturnTrue(): void
    {
        $tokenString = 'TOKEN_STRING';
        $tokenData = [
            'token' => $tokenString,
            'ts' => 12345,
        ];
        $token = new Token($tokenData);
        $this->tokenStoreMock
            ->method('getToken')
            ->with($tokenString)
            ->willReturn($token);

        self::assertTrue($this->subject->checkToken($tokenString), 'Method must return TRUE for valid token string.');
    }

    public function testCheckToken_WhenTokeDontExist_ThenReturnFalse(): void
    {
        $tokenString = 'TOKEN_STRING';
        $this->tokenStoreMock
            ->method('getToken')
            ->with($tokenString)
            ->willReturn(null);

        self::assertFalse(
            $this->subject->checkToken($tokenString),
            'Method must return FALSE when token does not exist.'
        );
    }

    public function testCheckToken_WhenInvalidTokenString_ThenReturnFalse(): void
    {
        $tokenString = 'TOKEN_STRING';
        $tokenData = [
            'token' => $tokenString,
            'ts' => 12345,
        ];
        $token1 = new Token($tokenData);
        $token2 = new Token();

        $this->tokenStoreMock
            ->method('getToken')
            ->with($tokenString)
            ->willReturn($token2);

        $this->subject->checkToken($token1);

        self::assertFalse(
            $this->subject->checkToken($token1),
            'Method must return FALSE when value does not match.'
        );
    }

    public function testCheckToken_WhenValidExpiredTokenTimeLimitOn_ThenReturnFalse(): void
    {
        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'poolSize' => 10,
                'timeLimit' => 1,
                'validateTokens' => true
            ]
        );

        $tokenString = 'TOKEN_STRING';
        $tokenData = [
            'token' => $tokenString,
            'ts' => microtime(true) - 100,
        ];
        $token = new Token($tokenData);
        $this->tokenStoreMock
            ->method('getToken')
            ->with($tokenString)
            ->willReturn($token);

        self::assertFalse(
            $this->subject->checkToken($token),
            'Method must return FALSE when token is expired.'
        );
    }

    public function testCheckToken_WhenValidExpiredTokenTimeLimitOff_ThenReturnTrue(): void
    {
        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'poolSize' => 10,
                'timeLimit' => 0, // Big time limit for token to not expire during test execution
                'validateTokens' => true
            ]
        );

        $tokenString = 'TOKEN_STRING';
        $tokenData = [
            'token' => $tokenString,
            'ts' => microtime(true),
        ];
        $token = new Token($tokenData);
        $this->tokenStoreMock
            ->method('getToken')
            ->with($tokenString)
            ->willReturn($token);

        self::assertTrue(
            $this->subject->checkToken($token),
            'Method must return TRUE when token value match and token is not expired.'
        );
    }

    public function testCheckFormToken_WhenCalled_ThenWillCheckCorrectToken(): void
    {
        $tokenString = 'TOKEN_STRING';
        $tokenData = [
            'token' => $tokenString,
            'ts' => 12345,
        ];
        $token = new Token($tokenData);

        // Assert that token retrieved using form namespace
        $this->tokenStoreMock
            ->expects(self::once())
            ->method('getToken')
            ->with(TokenService::FORM_TOKEN_NAMESPACE)
            ->willReturn($token);

        self::assertTrue($this->subject->checkFormToken($token), 'Method must return TRUE for valid form token object.');
    }

    public function testInvalidateRemovesExpiredTokens(): void
    {
        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'poolSize' => 10,
                'timeLimit' => 1,
                'validateTokens' => true
            ]
        );

        $tokenString1 = 'TOKEN_1';
        $expiredTokenData1 = [
            'token' => $tokenString1,
            'ts' => microtime(true) - 1000,
        ];
        $tokenString2 = 'TOKEN_2';
        $expiredTokenData2 = [
            'token' => $tokenString2,
            'ts' => microtime(true) - 1000,
        ];
        $tokenString3 = 'TOKEN_3';
        $validTokenData = [
            'token' => $tokenString3,
            'ts' => microtime(true) + 1000,
        ];
        $expiredToken1 = new Token($expiredTokenData1);
        $expiredToken2 = new Token($expiredTokenData2);
        $validToken = new Token($validTokenData);
        $tokensPool = [$expiredToken1, $expiredToken2, $validToken];

        // Assert that expired tokens will be deleted.
        $this->tokenStoreMock
            ->method('getAll')
            ->willReturn($tokensPool);
        $this->tokenStoreMock
            ->expects(self::exactly(2))
            ->method('removeToken')
            ->withConsecutive(
                [$tokenString1],
                [$tokenString2]
            );

        $this->subject->createToken();
    }

    public function testInvalidateRemovesOldestTokensWhenPoolIfFull(): void
    {
        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'poolSize' => 2,
                'timeLimit' => 1000,
                'validateTokens' => true
            ]
        );

        $tokenString1 = 'TOKEN_1';
        $tokenData1 = [
            'token' => $tokenString1,
            'ts' => microtime(true) - 3,
        ];
        $tokenString2 = 'TOKEN_2';
        $tokenData2 = [
            'token' => $tokenString2,
            'ts' => microtime(true) - 2,
        ];
        $tokenString3 = 'TOKEN_3';
        $tokenData3 = [
            'token' => $tokenString3,
            'ts' => microtime(true),
        ];
        $token1 = new Token($tokenData1);
        $token2 = new Token($tokenData2);
        $token3 = new Token($tokenData3);
        $tokensPool = [$token1, $token2, $token3];

        // Assert that oldest tokens will be deleted when pool is full.
        $this->tokenStoreMock
            ->method('getAll')
            ->willReturn($tokensPool);
        $this->tokenStoreMock
            ->expects(self::exactly(2))
            ->method('removeToken')
            ->withConsecutive(
                [$tokenString1],
                [$tokenString2]
            );

        $this->subject->createToken();
    }

    /**
     * @param int $poolSizeOption
     * @param bool $withForm
     * @param bool $hasFormToken
     * @param int $expectedResult
     * @throws InvalidService
     *
     * @dataProvider dataProviderTestGetPoolSize
     */
    public function testGetPoolSize(int $poolSizeOption, bool $withForm, bool $hasFormToken, int $expectedResult): void
    {
        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'poolSize' => $poolSizeOption,
                'timeLimit' => 0,
                'validateTokens' => true
            ]
        );

        $this->tokenStoreMock
            ->method('hasToken')
            ->with('form_token')
            ->willReturn($hasFormToken);

        $result = $this->subject->getPoolSize($withForm);

        self::assertSame($expectedResult, $result, 'Method must return correct pool size value.');
    }

    public function testGetPoolSize_WhenSizeNotConfigured_WillReturnDefaultValue(): void
    {
        $defaultPoolSize = 6;
        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'timeLimit' => 0,
                'validateTokens' => true
            ]
        );

        $result = $this->subject->getPoolSize();

        self::assertSame($defaultPoolSize, $result, 'Method must return default pool size if it is not configured.');
    }

    public function testGenerateTokenPool_WhenNoTokensStored_ThenGeneratesNewPool(): void
    {
        $poolSize = 5;
        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'poolSize' => $poolSize,
                'timeLimit' => 0,
                'validateTokens' => true
            ]
        );

        $this->tokenStoreMock
            ->method('getAll')
            ->willReturn([]);

        $this->tokenStoreMock
            ->expects(self::exactly($poolSize))
            ->method('setToken');

        $result = $this->subject->generateTokenPool();

        self::assertCount($poolSize, $result, 'Method must return a tokens pool of correct size.');
    }

    public function testGenerateTokenPool_WhenStoredPoolNotFull_ThenGeneratesMissingTokens(): void
    {
        $poolSize = 5;
        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'poolSize' => $poolSize,
                'timeLimit' => 0,
                'validateTokens' => true
            ]
        );

        $storedTokens = [
            new Token(),
            new Token(),
        ];

        $this->tokenStoreMock
            ->method('getAll')
            ->willReturn($storedTokens);

        $this->tokenStoreMock
            ->expects(self::exactly(3))
            ->method('setToken');

        $result = $this->subject->generateTokenPool();

        self::assertCount($poolSize, $result, 'Method must return a tokens pool of correct size.');
    }

    public function testGenerateTokenPool_WhenStoredPoolIsFull_ThenReturnStoredTokens(): void
    {
        $poolSize = 3;
        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'poolSize' => $poolSize,
                'timeLimit' => 0,
                'validateTokens' => true
            ]
        );

        $storedTokens = [
            new Token(),
            new Token(),
            new Token(),
        ];

        $this->tokenStoreMock
            ->method('getAll')
            ->willReturn($storedTokens);

        $this->tokenStoreMock
            ->expects(self::never())
            ->method('setToken');

        $result = $this->subject->generateTokenPool();

        self::assertCount($poolSize, $result, 'Method must return a tokens pool of correct size.');
        self::assertSame($storedTokens, $result, 'Method must return a pool of stored tokens.');
    }

    public function testGenerateTokenPool_WhenStoredPoolHasExpiredTokens_ThenGeneratesNewTokens(): void
    {
        $poolSize = 3;
        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'poolSize' => $poolSize,
                'timeLimit' => 1000,
                'validateTokens' => true
            ]
        );

        $expiredTokenValue = 'EXPIRED_TOKEN_VALUE';
        $expiredTokenData = [
            'token' => $expiredTokenValue,
            'ts' => microtime(true) - 1000,
        ];

        $storedTokens = [
            new Token(),
            new Token(),
            new Token($expiredTokenData)
        ];

        $this->tokenStoreMock
            ->method('getAll')
            ->willReturn($storedTokens);
        $this->tokenStoreMock
            ->expects(self::once())
            ->method('removeToken')
            ->with($expiredTokenValue);
        $this->tokenStoreMock
            ->expects(self::once())
            ->method('setToken');

        $result = $this->subject->generateTokenPool();

        self::assertCount($poolSize, $result, 'Method must return a tokens pool of correct size.');
    }

    public function testGetClientConfig(): void
    {
        $poolSize = 5;
        $this->subject = new TokenService(
            [
                'store' => $this->tokenStoreMock,
                'poolSize' => $poolSize,
                'timeLimit' => 60,
                'validateTokens' => true
            ]
        );

        $result = $this->subject->getClientConfig();

        self::assertArrayHasKey('tokenTimeLimit', $result, 'Client config must contain time limit value.');
        self::assertArrayHasKey('maxSize', $result, 'Client config must contain pool size value.');
        self::assertArrayHasKey('tokens', $result, 'Client config must contain list of stored tokens.');
        self::assertArrayHasKey('validateTokens', $result, 'Client config must contain validate tokens flag.');
        self::assertArrayHasKey('store', $result, 'Client config must contain client tokens store configuration.');
    }

    public function testAddFormToken(): void
    {
        $this->tokenStoreMock
            ->expects(self::once())
            ->method('setToken')
            ->with(
                'form_token',
                self::callback(function (Token $token) {
                    return true;
                })
            );

        $this->subject->addFormToken();
    }

    public function testGetFormToken_WhenFormTokenExist_ReturnStoredToken(): void
    {
        $storedFormToken = new Token();
        $this->tokenStoreMock
            ->method('getToken')
            ->with('form_token')
            ->willReturn($storedFormToken);

        $result = $this->subject->getFormToken();

        self::assertSame($storedFormToken, $result, 'Method must return form tokens from tokens store.');
    }

    public function testGetFormToken_WhenFormTokenDontExist_ReturnStoredToken(): void
    {
        // Form token does not exist in token store during the first call but exists during the second call.
        $this->tokenStoreMock
            ->method('getToken')
            ->with('form_token')
            ->willReturnOnConsecutiveCalls(
                null,
                new Token()
            );

        // Assert that new form token is generated and stored
        $this->tokenStoreMock
            ->expects(self::once())
            ->method('setToken')
            ->with(
                'form_token',
                self::callback(function (Token $token) {
                    return true;
                })
            );

        $this->subject->getFormToken();
    }


    public function dataProviderTestGetPoolSize(): array
    {
        return [
            'With form, with form token in storage' => [
                'poolSizeOption' => 10,
                'withForm' => true,
                'hasFormToken' => true,
                'expectedResult' => 11,
            ],
            'Without form, with form token in storage' => [
                'poolSizeOption' => 10,
                'withForm' => false,
                'hasFormToken' => true,
                'expectedResult' => 10,
            ],
            'With form, without form token in storage' => [
                'poolSizeOption' => 10,
                'withForm' => true,
                'hasFormToken' => false,
                'expectedResult' => 10,
            ],
            'Without form, without form token in storage' => [
                'poolSizeOption' => 10,
                'withForm' => false,
                'hasFormToken' => false,
                'expectedResult' => 10,
            ],
        ];
    }

    private function createTokenToValidate(): Token
    {
        $token = $this->createStoredToken();

        $this->tokenStoreMock
            ->expects(static::once())
            ->method('removeToken')
            ->with($token->getValue())
            ->willReturn(true);

        return $token;
    }

    private function createStoredToken(): Token
    {
        $token = $this->subject->createToken();

        $this->tokenStoreMock
            ->method('getToken')
            ->with($token->getValue())
            ->willReturn($token);

        return $token;
    }
}
