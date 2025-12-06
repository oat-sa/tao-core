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

    use oat\tao\test\unit\session\Business\Service\SessionCookieServiceTest;

    function session_get_cookie_params(): array
    {
        return SessionCookieServiceTest::makeMockFunctionCall('session_get_cookie_params');
    }

    function session_set_cookie_params(): bool
    {
        return SessionCookieServiceTest::makeMockFunctionCall('session_set_cookie_params', func_get_args());
    }

    function session_name(): string
    {
        return SessionCookieServiceTest::makeMockFunctionCall('session_name', func_get_args());
    }

    function session_start(): bool
    {
        return SessionCookieServiceTest::makeMockFunctionCall('session_start', func_get_args());
    }

    function time(): int
    {
        return SessionCookieServiceTest::makeMockFunctionCall('time');
    }

    function session_id(): string
    {
        return SessionCookieServiceTest::makeMockFunctionCall('session_id', func_get_args());
    }

    function setcookie(): bool
    {
        return SessionCookieServiceTest::makeMockFunctionCall('setcookie', func_get_args());
    }
}

namespace oat\tao\test\unit\session\Business\Service {

    use common_http_Request as Request;
    use PHPUnit\Framework\TestCase;
    use oat\tao\model\session\Business\Contract\SessionCookieAttributesFactoryInterface;
    use oat\tao\model\session\Business\Domain\SessionCookieAttribute;
    use oat\tao\model\session\Business\Domain\SessionCookieAttributeCollection;
    use oat\tao\model\session\Business\Service\SessionCookieService;
    use tao_helpers_Uri as UriHelper;

    /**
     * @covers \oat\tao\model\session\Business\Service\SessionCookieService
     */
    class SessionCookieServiceTest extends TestCase
    {
        private const SESSION_ATTRIBUTE_NAME  = 'test-name';
        private const SESSION_ATTRIBUTE_VALUE = 'test-value';

        private const TIME = 1;

        private const SESSION_ID = 'test';

        /** @var array[] */
        private static $mockFunctions = [];

        /** @var SessionCookieService */
        private $sut;

        public static function makeMockFunctionCall(string $function, array $arguments = [])
        {
            $definition = &self::$mockFunctions[$function];

            if ($definition) {
                $definition['actualArguments'] = $arguments;

                return $definition['return'];
            }

            $function = "\\$function";

            return $function(...$arguments);
        }

        /**
         * @before
         * @afterClass
         */
        public static function resetGlobalFunctionExpectations(): void
        {
            self::$mockFunctions = [];
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
            foreach (self::$mockFunctions as $globalFunctionExpectation) {
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
                    'domain' => 'test0.com',
                    'lifetime' => 0,
                ],
                [
                    'domain' => 'test1.com',
                    'lifetime' => 1,
                ],
            ];
        }

        private static function setGlobalFunctionExpectations(string $function, $return, ...$arguments): void
        {
            self::$mockFunctions[$function] = compact('return', 'arguments');
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
            self::setGlobalFunctionExpectations('session_get_cookie_params', compact('domain', 'lifetime'));
            self::setGlobalFunctionExpectations(
                'session_set_cookie_params',
                true,
                $this->createSessionCookieParams($lifetime, $domain, false)
            );
            self::setGlobalFunctionExpectations('session_name', GENERIS_SESSION_NAME, GENERIS_SESSION_NAME);
        }

        private function expectSessionReStart(): void
        {
            self::setGlobalFunctionExpectations('session_start', true);
        }

        private function expectSessionCookieReset(int $lifetime, string $domain): void
        {
            self::setGlobalFunctionExpectations('time', self::TIME);
            self::setGlobalFunctionExpectations('session_id', self::SESSION_ID);
            self::setGlobalFunctionExpectations(
                'setcookie',
                true,
                GENERIS_SESSION_NAME,
                self::SESSION_ID,
                $this->createSessionCookieParams($lifetime, $domain, true)
            );
        }

        private function createSessionCookieParams(int $lifetime, string $domain, bool $reset): array
        {
            $params = [];
            $params[self::SESSION_ATTRIBUTE_NAME] = self::SESSION_ATTRIBUTE_VALUE;
            if ($reset) {
                $params['expires'] = $lifetime + self::TIME;
            } else {
                $params['lifetime'] = $lifetime;
            }
            $params['domain'] = $this->getCookieDomain($domain);
            $params['secure'] = Request::isHttps();
            $params['httponly'] = true;
            return $params;
        }

        private function getCookieDomain(string $domain): string
        {
            return UriHelper::isValidAsCookieDomain(ROOT_URL)
                ? UriHelper::getDomain(ROOT_URL)
                : $domain;
        }
    }
}
