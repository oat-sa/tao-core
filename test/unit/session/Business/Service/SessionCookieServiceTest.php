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
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\session\Business\Service {

    function session_get_cookie_params(): array
    {
        return \oat\tao\test\unit\session\Business\Service\session_get_cookie_params();
    }

    function session_set_cookie_params(): bool
    {
        return \oat\tao\test\unit\session\Business\Service\session_set_cookie_params(...func_get_args());
    }

    function session_name(): string
    {
        return \oat\tao\test\unit\session\Business\Service\session_name(...func_get_args());
    }

    function session_start(): bool
    {
        return \oat\tao\test\unit\session\Business\Service\session_start(...func_get_args());
    }

    function time(): int
    {
        return \oat\tao\test\unit\session\Business\Service\time();
    }

    function session_id(): string
    {
        return \oat\tao\test\unit\session\Business\Service\session_id(...func_get_args());
    }

    function setcookie(): bool
    {
        return \oat\tao\test\unit\session\Business\Service\setcookie(...func_get_args());
    }
}

namespace oat\tao\test\unit\session\Business\Service {

    use common_http_Request as Request;
    use oat\generis\test\TestCase;
    use oat\tao\model\session\Business\Contract\SessionCookieAttributesFactoryInterface;
    use oat\tao\model\session\Business\Domain\SessionCookieAttribute;
    use oat\tao\model\session\Business\Domain\SessionCookieAttributeCollection;
    use oat\tao\model\session\Business\Service\SessionCookieService;
    use tao_helpers_Uri as UriHelper;

    /**
     * @covers \oat\tao\model\session\Business\Service\SessionCookieService
     * @runClassInSeparateProcess
     */
    class SessionCookieServiceTest extends TestCase
    {
        private const SESSION_ATTRIBUTE_NAME  = 'test-name';
        private const SESSION_ATTRIBUTE_VALUE = 'test-value';

        private const TIME = 1;

        private const SESSION_ID = 'test';

        /** @var SessionCookieService */
        private $sut;

        /**
         * @beforeClass
         */
        public static function initializeConfiguration(): void
        {
            define('ROOT_URL', 'http://test.com/');
            define('GENERIS_SESSION_NAME', 'test');
        }

        /**
         * @beforeClass
         * @after
         */
        public static function resetGlobalFunctionExpectations(): void
        {
            resetGlobalFunctionExpectations();
        }

        /**
         * @before
         */
        public function init(): void
        {
            $this->sut = new SessionCookieService(
                $this->createSessionCookieAttributeFactoryMock()
            );
        }

        /**
         * @after
         */
        public function assertGlobalFunctionCalls(): void
        {
            foreach (getGlobalFunctionExpectations() as $globalFunctionExpectation) {
                static::assertSame(
                    $globalFunctionExpectation['arguments'],
                    $globalFunctionExpectation['actualArguments']
                );
            }
        }

        /**
         * @param string $domain
         * @param int    $lifetime
         *
         * @dataProvider dataProvider
         */
        public function testInitializeSessionCookie(string $domain, int $lifetime): void
        {
            $this->expectCookieParametersCall($domain, $lifetime);

            $this->sut->initializeSessionCookie();
        }

        /**
         * @param string $domain
         * @param int    $lifetime
         *
         * @dataProvider dataProvider
         * @runInSeparateProcess
         */
        public function testReInitializeSessionCookie(string $domain, int $lifetime): void
        {
            $_COOKIE[GENERIS_SESSION_NAME] = 'test';

            $this->expectCookieParametersCall($domain, $lifetime);
            $this->expectSessionReStart();

            if (0 !== $lifetime) {
                $this->expectSessionCookieReset($lifetime, $domain);
            }

            $this->sut->initializeSessionCookie();
        }

        public function dataProvider(): array
        {
            return [
                [
                    'domain'   => 'test0.com',
                    'lifetime' => 0,
                ],
                [
                    'domain'   => 'test1.com',
                    'lifetime' => 1,
                ],
            ];
        }

        private function createSessionCookieAttributeFactoryMock(): SessionCookieAttributesFactoryInterface
        {
            $sessionCookieAttributesFactoryMock = $this->createMock(SessionCookieAttributesFactoryInterface::class);

            $sessionCookieAttributesFactoryMock
                ->expects(static::once())
                ->method('create')
                ->willReturn($this->createSessionCookieAttributeCollection());

            return $sessionCookieAttributesFactoryMock;
        }

        private function createSessionCookieAttributeCollection(): SessionCookieAttributeCollection
        {
            return (new SessionCookieAttributeCollection())
                ->add(new SessionCookieAttribute(self::SESSION_ATTRIBUTE_NAME, self::SESSION_ATTRIBUTE_VALUE));
        }

        private function expectCookieParametersCall(string $domain, int $lifetime): void
        {
            setGlobalFunctionExpectations('session_get_cookie_params', compact('domain', 'lifetime'));
            setGlobalFunctionExpectations(
                'session_set_cookie_params',
                true,
                $lifetime,
                $this->createSessionCookieAttributeString(),
                $this->getCookieDomain($domain),
                Request::isHttps(),
                true
            );
            setGlobalFunctionExpectations('session_name', GENERIS_SESSION_NAME, GENERIS_SESSION_NAME);
        }

        private function expectSessionReStart(): void
        {
            setGlobalFunctionExpectations('session_start', true);
        }

        private function expectSessionCookieReset(int $lifetime, string $domain): void
        {
            setGlobalFunctionExpectations('time', self::TIME);
            setGlobalFunctionExpectations('session_id', self::SESSION_ID);
            setGlobalFunctionExpectations(
                'setcookie',
                true,
                GENERIS_SESSION_NAME,
                self::SESSION_ID,
                $lifetime + self::TIME,
                $this->createSessionCookieAttributeString(),
                $this->getCookieDomain($domain),
                Request::isHttps(),
                true
            );
        }

        private function createSessionCookieAttributeString(): string
        {
            return sprintf('%s=%s', self::SESSION_ATTRIBUTE_NAME, self::SESSION_ATTRIBUTE_VALUE);
        }

        private function getCookieDomain(string $domain): string
        {
            return UriHelper::isValidAsCookieDomain(ROOT_URL)
                ? UriHelper::getDomain(ROOT_URL)
                : $domain;
        }
    }

    function resetGlobalFunctionExpectations(): void
    {
        global $mockFunctions;

        $mockFunctions = [];
    }

    function setGlobalFunctionExpectations(string $function, $return, ...$arguments): void
    {
        global $mockFunctions;

        $mockFunctions[$function] = compact('return', 'arguments');
    }

    function getGlobalFunctionExpectations(): array
    {
        global $mockFunctions;

        return $mockFunctions;
    }

    function session_get_cookie_params(): array
    {
        global $mockFunctions;

        $definition = &$mockFunctions['session_get_cookie_params'];

        if ($definition) {
            $definition['actualArguments'] = func_get_args();

            return $definition['return'];
        }

        return \session_get_cookie_params();
    }

    function session_set_cookie_params(): bool
    {
        global $mockFunctions;

        $definition = &$mockFunctions['session_set_cookie_params'];

        if ($definition) {
            $definition['actualArguments'] = func_get_args();

            return $definition['return'];
        }

        return \session_set_cookie_params(...func_get_args());
    }

    function session_name(): string
    {
        global $mockFunctions;

        $definition = &$mockFunctions['session_name'];

        if ($definition) {
            $definition['actualArguments'] = func_get_args();

            return $definition['return'];
        }

        return \session_name(...func_get_args());
    }

    function session_start(): bool
    {
        global $mockFunctions;

        $definition = &$mockFunctions['session_start'];

        if ($definition) {
            $definition['actualArguments'] = func_get_args();

            return $definition['return'];
        }

        return \session_start(...func_get_args());
    }

    function time(): int
    {
        global $mockFunctions;

        $definition = &$mockFunctions['time'];

        if ($definition) {
            $definition['actualArguments'] = func_get_args();

            return $definition['return'];
        }

        return \time();
    }

    function session_id(): string
    {
        global $mockFunctions;

        $definition = &$mockFunctions['session_id'];

        if ($definition) {
            $definition['actualArguments'] = func_get_args();

            return $definition['return'];
        }

        return \session_id(...func_get_args());
    }

    function setcookie(): bool
    {
        global $mockFunctions;

        $definition = &$mockFunctions['setcookie'];

        if ($definition) {
            $definition['actualArguments'] = func_get_args();

            return $definition['return'];
        }

        return \setcookie(...func_get_args());
    }
}
