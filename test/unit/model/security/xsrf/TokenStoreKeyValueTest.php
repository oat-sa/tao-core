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
 * Copyright (c) 2017-2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\security\xsrf;

use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\user\User;
use PHPUnit\Framework\MockObject\MockObject;
use oat\oatbox\session\SessionService;
use oat\tao\model\security\xsrf\Token;
use common_persistence_AdvKeyValuePersistence;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\security\xsrf\TokenStoreKeyValue;
use PHPUnit\Framework\TestCase;

class TokenStoreKeyValueTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const PERSISTENCE_NAME = 'ADVANCED_KV_PERSISTENCE';
    private const USER_IDENTIFIER = 'CURRENT_USER_IDENTIFIER';
    private const TOKEN = 'TOKEN';
    private const KEY = self::USER_IDENTIFIER . '_' . TokenStoreKeyValue::TOKENS_STORAGE_KEY;

    private TokenStoreKeyValue $subject;

    /** @var common_persistence_AdvKeyValuePersistence|MockObject */
    private common_persistence_AdvKeyValuePersistence $persistenceMock;

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
            $this->getServiceManagerMock(
                [
                    PersistenceManager::SERVICE_ID => $persistenceManagerMock,
                    SessionService::SERVICE_ID => $sessionServiceMock,
                ]
            )
        );
    }

    public function testGetTokenWhenTokenExistsReturnToken(): void
    {
        $tokenData = [
            'token' => self::TOKEN,
            'ts' => 12345,
        ];

        $this->persistenceMock
            ->method('hGet')
            ->with(self::KEY, self::TOKEN)
            ->willReturn(json_encode($tokenData));

        $result = $this->subject->getToken(self::TOKEN);

        $this->assertInstanceOf(
            Token::class,
            $result,
            'Method must return instance of Token.'
        );
        $this->assertSame(
            self::TOKEN,
            $result->getValue(),
            'Token value must be as expected'
        );
        $this->assertSame(
            $tokenData['ts'],
            $result->getCreatedAt(),
            'Token createdAt value must be as expected'
        );
    }

    public function testGetTokenWhenTokenDontExistsReturnNull(): void
    {
        $this->persistenceMock
            ->method('hGet')
            ->willReturn(false);

        $this->assertNull(
            $this->subject->getToken('TOKEN_STRING'),
            'Method must return NULL if token not found.'
        );
    }

    public function testSetToken(): void
    {
        $token = new Token(
            [
                'token' => self::TOKEN,
                'ts' => 12345,
            ]
        );

        $this->persistenceMock
            ->expects($this->once())
            ->method('hSet')
            ->with(self::KEY, self::TOKEN, json_encode($token));

        $this->subject->setToken(self::TOKEN, $token);
    }

    public function testHasTokenWhenTokenExistsThenReturnTrue(): void
    {
        $this->persistenceMock
            ->method('hExists')
            ->with(self::KEY, self::TOKEN)
            ->willReturn(true);

        $this->assertTrue(
            $this->subject->hasToken(self::TOKEN),
            'Method must return TRUE if token exists.'
        );
    }

    public function testHasTokenWhenTokenDontExistsThenReturnFalse(): void
    {
        $this->persistenceMock
            ->method('hExists')
            ->with(self::KEY, self::TOKEN)
            ->willReturn(false);

        $this->assertFalse(
            $this->subject->hasToken(self::TOKEN),
            'Method must return FALSE if token does not exists.'
        );
    }

    public function testRemoveTokenWhenTokenWasRemovedThenReturnTrue(): void
    {
        $this->persistenceMock
            ->method('hDel')
            ->with(self::KEY, self::TOKEN)
            ->willReturn(true);

        $this->assertTrue(
            $this->subject->removeToken(self::TOKEN),
            'Method must return TRUE when token removed.'
        );
    }

    public function testRemoveTokenWhenTokenWasNotRemovedThenReturnFalse(): void
    {
        $this->persistenceMock
            ->method('hDel')
            ->with(self::KEY, self::TOKEN)
            ->willReturn(false);

        $this->assertFalse(
            $this->subject->removeToken(self::TOKEN),
            'Method must return FALSE when token was not removed.'
        );
    }

    public function testClearWhenThereAreTokensStoredRemoveAllTokens(): void
    {
        $this->persistenceMock
            ->expects($this->once())
            ->method('del')
            ->with(self::KEY);

        $this->subject->clear();
    }

    public function testGetAllWithSuccess(): void
    {
        $jsonToken1 = '{"token": "TOKEN_VALUE_1","ts":12345}';
        $jsonToken2 = '{"token": "TOKEN_VALUE_2","ts":6789}';
        $tokensData = [
            'TOKEN_ID_1' => $jsonToken1,
            'TOKEN_ID_2' => $jsonToken2,
        ];

        $this->persistenceMock
            ->method('hGetAll')
            ->with(self::KEY)
            ->willReturn($tokensData);

        $this->assertEquals(
            [
                new Token(json_decode($jsonToken1, true)),
                new Token(json_decode($jsonToken2, true)),
            ],
            $this->subject->getAll()
        );
    }

    public function testGetAllWillReturnEmptyWhenNoStoredTokens(): void
    {
        $this->persistenceMock
            ->method('hGetAll')
            ->willReturn([]);

        $this->assertEquals([], $this->subject->getAll());
    }

    public function testGetAllWithReturnEmptyWhenInvalidKey(): void
    {
        $this->persistenceMock
            ->method('hGetAll')
            ->willReturn(
                [
                    false,
                    0,
                    null
                ]
            );

        $this->assertEquals([], $this->subject->getAll());
    }
}
