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
 * Copyright (c) 2017-2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\security\xsrf;

use oat\oatbox\user\User;
use oat\generis\test\TestCase;
use oat\generis\test\MockObject;
use oat\oatbox\session\SessionService;
use oat\tao\model\security\xsrf\Token;
use common_persistence_AdvKeyValuePersistence;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\security\xsrf\TokenStoreKeyValue;

class TokenStoreKeyValueTest extends TestCase
{
    private const PERSISTENCE_NAME = 'ADVANCED_KV_PERSISTENCE';
    private const USER_IDENTIFIER = 'CURRENT_USER_IDENTIFIER';
    private const TOKEN = 'TOKEN';
    private const KEY = self::USER_IDENTIFIER . '_' . TokenStoreKeyValue::TOKENS_STORAGE_KEY . '_' . self::TOKEN;

    /** @var TokenStoreKeyValue */
    private $subject;

    /** @var common_persistence_AdvKeyValuePersistence|MockObject */
    private $persistenceMock;

    protected function setUp(): void
    {
        $this->persistenceMock = $this->createMock(common_persistence_AdvKeyValuePersistence::class);

        $persistenceManagerMock = $this->createMock(PersistenceManager::class);
        $persistenceManagerMock
            ->method('getPersistenceById')
            ->with(self::PERSISTENCE_NAME)
            ->willReturn($this->persistenceMock);

        $userMock = $this->createMock(User::class);
        $userMock
            ->method('getIdentifier')
            ->willReturn(self::USER_IDENTIFIER);

        $sessionServiceMock = $this->createMock(SessionService::class);
        $sessionServiceMock
            ->method('getCurrentUser')
            ->willReturn($userMock);

        $this->subject = new TokenStoreKeyValue(['persistence' => self::PERSISTENCE_NAME]);
        $this->subject->setServiceManager(
            $this->getServiceLocatorMock(
                [
                    PersistenceManager::SERVICE_ID => $persistenceManagerMock,
                    SessionService::SERVICE_ID => $sessionServiceMock,
                ]
            )
        );
    }

    public function testGetToken_WhenTokenExists_ReturnToken(): void
    {
        $tokenData = [
            'token' => self::TOKEN,
            'ts' => 12345.0,
        ];

        $this->persistenceMock
            ->method('exists')
            ->with(self::KEY)
            ->willReturn(true);
        $this->persistenceMock
            ->method('get')
            ->with(self::KEY)
            ->willReturn(json_encode($tokenData));

        $result = $this->subject->getToken(self::TOKEN);

        $this->assertInstanceOf(
            Token::class,
            $result,
            'Method must return instance of Token.'
        );
        $this->assertSame(
            $tokenData['token'],
            $result->getValue(),
            'Token value must be as expected'
        );
        $this->assertSame(
            $tokenData['ts'],
            $result->getCreatedAt(),
            'Token createdAt value must be as expected'
        );
    }

    public function testGetToken_WhenTokenDontExists_ReturnNull(): void
    {
        $this->persistenceMock
            ->method('exists')
            ->willReturn(false);

        $this->assertNull(
            $this->subject->getToken('TOKEN_STRING'),
            'Method must return NULL if token not found.'
        );
    }

    public function testSetToken(): void
    {
        $token = $this->createMock(Token::class);
        $token
            ->method('jsonSerialize')
            ->willReturn([]);

        $this->persistenceMock
            ->expects($this->once())
            ->method('set')
            ->with(self::KEY, json_encode($token));

        $this->subject->setToken(self::TOKEN, $token);
    }

    public function testHasToken_WhenTokenExists_ThenReturnTrue(): void
    {
        $this->persistenceMock
            ->method('exists')
            ->with(self::KEY)
            ->willReturn(true);

        $this->assertTrue(
            $this->subject->hasToken(self::TOKEN),
            'Method must return TRUE if token exists.'
        );
    }

    public function testHasToken_WhenTokenDontExists_ThenReturnFalse(): void
    {
        $this->persistenceMock
            ->method('exists')
            ->with(self::KEY)
            ->willReturn(false);

        $this->assertFalse(
            $this->subject->hasToken(self::TOKEN),
            'Method must return FALSE if token does not exists.'
        );
    }

    public function testRemoveToken_WhenTokenWasRemoved_ThenReturnTrue(): void
    {
        $this->persistenceMock
            ->method('exists')
            ->with(self::KEY)
            ->willReturn(true);

        $this->persistenceMock
            ->method('del')
            ->with(self::KEY)
            ->willReturn(true);

        $this->assertTrue(
            $this->subject->removeToken(self::TOKEN),
            'Method must return TRUE when token removed.'
        );
    }

    public function testRemoveToken_WhenTokenWasNotRemoved_ThenReturnTrue(): void
    {
        $this->persistenceMock
            ->method('exists')
            ->with(self::KEY)
            ->willReturn(true);

        $this->persistenceMock
            ->method('del')
            ->with(self::KEY)
            ->willReturn(false);

        $this->assertFalse(
            $this->subject->removeToken(self::TOKEN),
            'Method must return FALSE when token was not removed.'
        );
    }

    public function testRemoveToken_WhenTokenDoesNotExists_ThenReturnFalse(): void
    {
        $this->persistenceMock
            ->method('exists')
            ->with(self::KEY)
            ->willReturn(false);

        $this->assertFalse(
            $this->subject->removeToken(self::TOKEN),
            'Method must return FALSE when token do not exist.'
        );
    }

    public function testClear_WhenThereAreTokensStored_RemoveAllTokens(): void
    {
        $this->persistenceMock
            ->expects($this->once())
            ->method('keys')
            ->willReturn([self::KEY]);
        $this->persistenceMock
            ->expects($this->once())
            ->method('del')
            ->with(self::KEY);

        $this->subject->clear();
    }

    /**
     * @param mixed $tokensData
     * @param array $expected
     *
     * @dataProvider dataProviderTestGetAllTokens
     */
    public function testGetAll($tokensData, array $expected): void
    {
        $keys = is_array($tokensData)
            ? array_keys($tokensData)
            : $tokensData;

        $this->persistenceMock
            ->expects($this->once())
            ->method('keys')
            ->willReturn($keys);
        $this->persistenceMock
            ->method('get')
            ->willReturnCallback(
                static function (string $key) use ($tokensData): string {
                    return $tokensData[$key];
                }
            );

        $result = $this->subject->getAll();

        $this->assertEquals($expected, $result);
    }

    public function dataProviderTestGetAllTokens(): array
    {
        $jsonToken1 = '{"token": "TOKEN_VALUE_1","ts":12345.0}';
        $jsonToken2 = '{"token": "TOKEN_VALUE_2","ts":6789}';

        return [
            'List of tokens stored' => [
                'tokensData' => [
                    'TOKEN_ID_1' => $jsonToken1,
                    'TOKEN_ID_2' => $jsonToken2,
                ],
                'expected' => [
                    new Token(json_decode($jsonToken1, true)),
                    new Token(json_decode($jsonToken2, true)),
                ]
            ],
            'No stored tokens' => [
                'tokensData' => [],
                'expected' => [],
            ],
            'Not valid storage response' => [
                'tokensData' => false,
                'expected' => [],
            ]
        ];
    }
}
