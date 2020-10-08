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

use common_persistence_AdvKeyValuePersistence;
use oat\generis\persistence\PersistenceManager;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;
use oat\tao\model\security\xsrf\Token;
use oat\tao\model\security\xsrf\TokenStoreKeyValue;

/**
 * Unit Tests for oat\tao\model\security\TokenStoreKeyValue
 */
class TokenStoreKeyValueTest extends TestCase
{
    private const PERSISTENCE_NAME = 'ADVANCED_KV_PERSISTENCE';
    private const USER_IDENTIFIER = 'CURRENT_USER_IDENTIFIER';

    /**
     * @var TokenStoreKeyValue
     */
    private $subject;

    /**
     * @var common_persistence_AdvKeyValuePersistence|MockObject
     */
    private $persistenceMock;

    /**
     * @var User|MockObject
     */
    private $userMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->persistenceMock = $this->createMock(common_persistence_AdvKeyValuePersistence::class);
        $persistenceManagerMock = $this->createMock(PersistenceManager::class);
        $persistenceManagerMock->method('getPersistenceById')
            ->with(self::PERSISTENCE_NAME)
            ->willReturn($this->persistenceMock);

        $this->userMock = $this->createMock(User::class);
        $this->userMock->method('getIdentifier')
            ->willReturn(self::USER_IDENTIFIER);
        $sessionServiceMock = $this->createMock(SessionService::class);
        $sessionServiceMock->method('getCurrentUser')
            ->willReturn($this->userMock);
        $serviceLocatorMock = $this->getServiceLocatorMock(
            [
                PersistenceManager::SERVICE_ID => $persistenceManagerMock,
                SessionService::SERVICE_ID => $sessionServiceMock,
            ]
        );

        $this->subject = new TokenStoreKeyValue(['persistence' => self::PERSISTENCE_NAME]);
        $this->subject->setServiceLocator($serviceLocatorMock);
    }

    public function testGetToken_WhenTokenExists_ReturnToken(): void
    {
        $tokenId = "TOKEN_STRING";
        $tokenData = [
            "token" => $tokenId,
            "ts" => 12345
        ];

        $key = self::USER_IDENTIFIER . '_tao_tokens';
        $this->persistenceMock
            ->method('hExists')
            ->with($key, $tokenId)
            ->willReturn(true);

        $this->persistenceMock
            ->method('hGet')
            ->with($key, $tokenId)
            ->willReturn(json_encode($tokenData));

        $result = $this->subject->getToken($tokenId);

        self::assertInstanceOf(Token::class, $result, 'Method must return instance of Token.');
        self::assertSame($tokenData['token'], $result->getValue(), 'Token value must be as expected');
        self::assertSame($tokenData['ts'], $result->getCreatedAt(), 'Token createdAt value must be as expected');
    }

    public function testGetToken_WhenTokenDontExists_ReturnNull(): void
    {
        $tokenId = "TOKEN_STRING";
        $this->persistenceMock
            ->method('hExists')
            ->willReturn(false);

        self::assertNull($this->subject->getToken($tokenId), 'Method must return NULL if token not found.');
    }

    public function testSetToken(): void
    {
        $tokenId = 'TOKEN_ID';
        $token = new Token();
        $this->persistenceMock
            ->expects(self::once())
            ->method('hSet')
            ->with(self::USER_IDENTIFIER . '_tao_tokens', $tokenId, json_encode($token));

        $this->subject->setToken($tokenId, $token);
    }

    public function testHasToken_WhenTokenExists_ThenReturnTrue(): void
    {
        $tokenId = 'TOKEN_STRING';

        $this->persistenceMock
            ->method('hExists')
            ->with(self::USER_IDENTIFIER . '_tao_tokens', $tokenId)
            ->willReturn(true);

        self::assertTrue($this->subject->hasToken($tokenId), 'Method must return TRUE if token exists.');
    }

    public function testHasToken_WhenTokenDontExists_ThenReturnFalse(): void
    {
        $tokenId = 'TOKEN_STRING';

        $this->persistenceMock
            ->method('hExists')
            ->with(self::USER_IDENTIFIER . '_tao_tokens', $tokenId)
            ->willReturn(false);

        self::assertFalse($this->subject->hasToken($tokenId), 'Method must return FALSE if token does not exists.');
    }

    public function testRemoveToken_WhenTokenWasRemoved_ThenReturnTrue(): void
    {
        $tokenId = 'TOKEN_STRING';

        $key = self::USER_IDENTIFIER . '_tao_tokens';
        $this->persistenceMock
            ->method('hExists')
            ->willReturn(true);

        $this->persistenceMock
            ->method('hDel')
            ->with($key, $tokenId)
            ->willReturn(true);

        self::assertTrue($this->subject->removeToken($tokenId), 'Method must return TRUE when token removed.');
    }

    public function testRemoveToken_WhenTokenWasNotRemoved_ThenReturnTrue(): void
    {
        $tokenId = 'TOKEN_STRING';

        $key = self::USER_IDENTIFIER . '_tao_tokens';
        $this->persistenceMock
            ->method('hExists')
            ->willReturn(true);

        $this->persistenceMock
            ->method('hDel')
            ->with($key, $tokenId)
            ->willReturn(false);

        self::assertFalse($this->subject->removeToken($tokenId), 'Method must return FALSE when token was not removed.');
    }

    public function testRemoveToken_WhenTokenDoesNotExists_ThenReturnFalse(): void
    {
        $tokenId = 'TOKEN_STRING';

        $this->persistenceMock
            ->method('hExists')
            ->willReturn(false);

        self::assertFalse($this->subject->removeToken($tokenId), 'Method must return FALSE when token do not exist.');
    }

    public function testClear_WhenThereAreTokensStored_RemoveAllTokens(): void
    {
        $this->persistenceMock
            ->expects(self::once())
            ->method('del');

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
        $this->persistenceMock
            ->method('hGetAll')
            ->willReturn($tokensData);

        $result = $this->subject->getAll();

        self::assertEquals($expected, $result);
    }

    public function getStoredTokensData(): array
    {
        return [
            'TOKEN_ID_1' => '{"token": "TOKEN_VALUE_1","ts":12345}',
            'TOKEN_ID_2' => '{"token": "TOKEN_VALUE_2","ts":6789}',
        ];
    }

    public function dataProviderTestGetAllTokens(): array
    {
        $jsonToken1 = '{"token": "TOKEN_VALUE_1","ts":12345}';
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
