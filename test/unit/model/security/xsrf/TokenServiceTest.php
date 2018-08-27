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

use oat\tao\test\TaoPhpUnitTestRunner;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\model\security\xsrf\TokenStore;
use oat\oatbox\service\exception\InvalidService;
use Prophecy\Argument;

/**
 * Unit Test of oat\tao\model\security\TokenGenerator
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class TokenServiceTest extends TaoPhpUnitTestRunner
{

    /**
     * @expectedException  oat\oatbox\service\exception\InvalidService
     */
    public function testInstantianteNoStore()
    {
        new TokenService();
    }

    /**
     * @expectedException  oat\oatbox\service\exception\InvalidService
     */
    public function testInstantianteBadStore()
    {
        new TokenService([
            'store' =>  []
        ]);
    }

    public function testCreateToken()
    {
        $store = $this->getStoreMock();
        $service = new TokenService([
            'store' =>  $store
        ]);

        $this->assertEquals(0, count($store->getTokens()), 'The store is empty');

        $token = $service->createToken();
        $this->assertEquals(40, strlen($token), 'The token has the expected length');
        $this->assertRegExp('/^[0-9a-f]{40}$/', $token, 'The token is correctly formatted');
        $this->assertEquals($store->getTokens()[0]['token'], $token, 'The store contains the correct token');

        $this->assertEquals(1, count($store->getTokens()), 'The store contains now one token');

        $token2 = $service->createToken();

        $this->assertEquals(40, strlen($token2), 'The token has the expected length');
        $this->assertRegExp('/^[0-9a-f]{40}$/', $token2, 'The token is correctly formatted');
        $this->assertEquals($store->getTokens()[1]['token'], $token2, 'The store contains the correct token');

        $this->assertEquals(2, count($store->getTokens()), 'The store contains now two tokens');

        $this->assertFalse($token == $token2, 'The tokens are differents');
    }

    public function testPoolSize()
    {
        $store = $this->getStoreMock();
        $service = new TokenService([
            'store' =>  $store,
            'poolSize' => 4
        ]);

        $this->assertEquals(0, count($store->getTokens()), 'The store is empty');

        $service->createToken();
        $this->assertEquals(1, count($store->getTokens()), 'The store contains now one token');

        $service->createToken();
        $this->assertEquals(2, count($store->getTokens()), 'The store contains now two tokens');

        $service->createToken();
        $this->assertEquals(3, count($store->getTokens()), 'The store contains now three tokens');

        $service->createToken();
        $this->assertEquals(4, count($store->getTokens()), 'The store contains now four tokens');

        $service->createToken();
        $this->assertEquals(4, count($store->getTokens()), 'The store remains at four tokens, the max pool size');

        $service->createToken();
        $this->assertEquals(4, count($store->getTokens()), 'The store remains at four tokens, the max pool size');
    }

    public function testRevokeToken()
    {
        $store = $this->getStoreMock();
        $service = new TokenService([
            'store' =>  $store
        ]);

        $this->assertEquals(0, count($store->getTokens()), 'The store is empty');

        $token = $service->createToken();

        $this->assertEquals(1, count($store->getTokens()), 'The store contains now one token');
        $this->assertEquals($store->getTokens()[0]['token'], $token, 'The store contains the correct token');

        $this->assertFalse($service->revokeToken('this is not a token'), 'If the token doen\'t exists it is not revoked');
        $this->assertEquals(1, count($store->getTokens()), 'The store still contains one token');

        $this->assertTrue($service->revokeToken($token), 'The token has been revoked');
        $this->assertEquals(0, count($store->getTokens()), 'The store doesn\'t contain the token anymore');
    }

    public function testCheckToken()
    {
        $store = $this->getStoreMock();
        $service = new TokenService([
            'store' =>  $store
        ]);

        $this->assertEquals(0, count($store->getTokens()), 'The store is empty');

        $token1 = $service->createToken();
        $token2 = $service->createToken();
        $this->assertEquals(2, count($store->getTokens()), 'The store contains the created tokens');

        $this->assertFalse($service->checkToken('wooch'), 'This token is not a token so it cannot be valid');
        $this->assertTrue($service->checkToken($token1), 'This token is valid');
        $this->assertTrue($service->checkToken($token1), 'This token is still valid');
        $this->assertTrue($service->checkToken($token2), 'This second token is also valid');

        $this->assertTrue($service->revokeToken($token1), 'The token has been revoked');

        $this->assertFalse($service->checkToken($token1), 'This token has been revoked');
        $this->assertTrue($service->checkToken($token2), 'This second token is still valid');
    }

    public function testInvalidateTimeLimit()
    {
        $store = $this->getStoreMock();
        $service = new TokenService([
            'store' =>  $store,
            'timeLimit' => 1
        ]);

        $this->assertEquals(0, count($store->getTokens()), 'The store is empty');

        $token1 = $service->createToken();

        $this->assertEquals(1, count($store->getTokens()), 'The store contains now one token');
        $this->assertTrue($service->checkToken($token1), 'This first token is valid');

        sleep(1);

        $token2 = $service->createToken();
        $this->assertEquals(1, count($store->getTokens()), 'The store contains only one token, the 1st has been invalidated');
        $this->assertFalse($service->checkToken($token1), 'This token has been revoked');

        sleep(1);

        $this->assertFalse($service->checkToken($token2), 'This token is not valid anymore');
    }

    public function testTimeLimit()
    {
        $store = $this->getStoreMock();
        $service = new TokenService([
            'store' =>  $store,
            'timeLimit' => 2
        ]);

        $this->assertEquals(0, count($store->getTokens()), 'The store is empty');

        $token1 = $service->createToken();

        $this->assertEquals(1, count($store->getTokens()), 'The store contains now one token');
        $this->assertTrue($service->checkToken($token1), 'This first token is valid');

        sleep(1);

        $token2 = $service->createToken();
        $this->assertEquals(2, count($store->getTokens()), 'The store contains the two tokens');
        $this->assertTrue($service->checkToken($token1), 'This first token is valid');
        $this->assertTrue($service->checkToken($token2), 'This second token is also valid');

        sleep(1);

        $this->assertFalse($service->checkToken($token1), 'This first token is not valid anymore');
        $this->assertTrue($service->checkToken($token2), 'This second token is still valid');

        sleep(1);

        $this->assertFalse($service->checkToken($token1), 'This first token is not valid');
        $this->assertFalse($service->checkToken($token2), 'This second token is not valid');
    }

    protected function getStoreMock()
    {
        $storeMock = $this->prophesize('oat\tao\model\security\xsrf\TokenStore');
        $storeMock->getTokens()->willReturn([]);
        $storeMock->setTokens(Argument::any())->will(function ($args) use ($storeMock){
            $storeMock->getTokens()->willReturn($args[0]);
        });
        return $storeMock->reveal();
    }
}
