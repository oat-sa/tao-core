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

use oat\generis\test\TestCase;
use oat\oatbox\session\SessionService;
use oat\tao\model\security\xsrf\Token;
use oat\tao\model\security\xsrf\TokenStoreKeyValue;
use Zend\ServiceManager\ServiceLocatorInterface;
use oat\generis\test\MockObject;

/**
 * Unit Tests for oat\tao\model\security\TokenStoreKeyValue
 */
class TokenStoreKeyValueTest extends TestCase
{
    /**
     * @dataProvider tokensToTest
     */
    public function testGetTokens($tokensAsJson, $expected)
    {
        $persistenceKey = 'a persistence key';

        $options = [TokenStoreKeyValue::OPTION_PERSISTENCE => $persistenceKey];
        $userIdentifier = 'a user\' identifier';

        /** @var \common_persistence_KeyValuePersistence|MockObject $persistence */
        $persistence = $this->getMockBuilder(\common_persistence_KeyValuePersistence::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $persistence->method('get')
            ->with($userIdentifier . '_' . TokenStoreKeyValue::TOKENS_STORAGE_KEY)
            ->willReturn($tokensAsJson);

        /** @var \common_persistence_Manager|MockObject $persistenceManager */
        $persistenceManager = $this->getMockBuilder(\common_persistence_Manager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPersistenceById'])
            ->getMock();
        $persistenceManager->method('getPersistenceById')->with($persistenceKey)->willReturn($persistence);

        /** @var \common_user_User|MockObject $sessionService */
        $user = $this->getMockBuilder(\common_user_User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdentifier'])
            ->getMockForAbstractClass();
        $user->method('getIdentifier')->willReturn($userIdentifier);

        /** @var SessionService|MockObject $sessionService */
        $sessionService = $this->getMockBuilder(SessionService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUser'])
            ->getMock();
        ;
        $sessionService->method('getCurrentUser')->willReturn($user);

        /** @var ServiceLocatorInterface|MockObject $serviceLocator */
        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $serviceLocator->method('get')->willReturnCallback(
            function ($class) use ($persistenceManager, $sessionService) {
                switch ($class) {
                    case \common_persistence_Manager::class:
                        return $persistenceManager;
                        break;
                    case SessionService::class:
                        return $sessionService;
                        break;
                    default:
                        return null;
                }
            }
        );

        $subject = new TokenStoreKeyValue($options);
        $subject->setServiceLocator($serviceLocator);

        $this->assertEquals($expected, $subject->getTokens());
    }

    public function tokensToTest()
    {
        $key1 = 'key1';
        $value1 = 'value1';
        $key2 = 'key2';
        $value2 = 'value2';

        return [
            ['[]', []],
            [
                '{"key1":"value1","key2":"value2"}',
                [$key1 => new Token($value1), $key2 => new Token($value2)],
            ],
        ];
    }
}
