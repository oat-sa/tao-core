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

namespace oat\tao\test\unit\session\Business\Service;

use common_Config as Config;
use common_http_Request as Request;
use oat\generis\test\TestCase;
use oat\tao\model\session\Business\Contract\SessionCookieAttributesFactoryInterface;
use oat\tao\model\session\Business\Domain\SessionCookieAttribute;
use oat\tao\model\session\Business\Domain\SessionCookieAttributeCollection;
use oat\tao\model\session\Business\Service\SessionCookieService;
use phpmock\phpunit\PHPMock;
use tao_helpers_Uri as UriHelper;

/**
 * @covers \oat\tao\model\session\Business\Service\SessionCookieService
 */
class SessionCookieServiceTest extends TestCase
{
    use PHPMock;

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
        Config::load();
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
            ],[
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
        $getCookieParametersMock = $this->getFunctionMock($this->getSutNamespace(), 'session_get_cookie_params');
        $getCookieParametersMock
            ->expects(static::once())
            ->willReturn(compact('domain', 'lifetime'));

        $setCookieParametersMock = $this->getFunctionMock($this->getSutNamespace(), 'session_set_cookie_params');
        $setCookieParametersMock
            ->expects(static::once())
            ->with(
                $lifetime,
                $this->createSessionCookieAttributeString(),
                $this->getCookieDomain($domain),
                Request::isHttps(),
                true
            )
            ->willReturn(true);

        $sessionNameMock = $this->getFunctionMock($this->getSutNamespace(), 'session_name');
        $sessionNameMock
            ->expects(static::once())
            ->with(GENERIS_SESSION_NAME)
            ->willReturn(GENERIS_SESSION_NAME);
    }

    private function expectSessionReStart(): void
    {
        $sessionStartMock = $this->getFunctionMock($this->getSutNamespace(), 'session_start');
        $sessionStartMock
            ->expects(static::once())
            ->willReturn(true);
    }

    private function expectSessionCookieReset(int $lifetime, string $domain): void
    {
        $timeMock = $this->getFunctionMock($this->getSutNamespace(), 'time');
        $timeMock
            ->expects(static::once())
            ->willReturn(self::TIME);

        $sessionIdMock = $this->getFunctionMock($this->getSutNamespace(), 'session_id');
        $sessionIdMock
            ->expects(static::once())
            ->willReturn(self::SESSION_ID);

        $setCookieMock = $this->getFunctionMock($this->getSutNamespace(), 'setcookie');
        $setCookieMock
            ->expects(static::once())
            ->with(
                GENERIS_SESSION_NAME,
                self::SESSION_ID,
                $lifetime + self::TIME,
                $this->createSessionCookieAttributeString(),
                $this->getCookieDomain($domain),
                Request::isHttps(),
                true
            )
            ->willReturn(true);
    }

    private function createSessionCookieAttributeString(): string
    {
        return sprintf('%s=%s', self::SESSION_ATTRIBUTE_NAME, self::SESSION_ATTRIBUTE_VALUE);
    }

    private function getSutNamespace(): string
    {
        static $namespace;

        if (null === $namespace) {
            $classNameParts = explode('\\', get_class($this->sut));

            array_pop($classNameParts);

            $namespace = implode('\\', $classNameParts);
        }

        return $namespace;
    }

    private function getCookieDomain(string $domain): string
    {
        return UriHelper::isValidAsCookieDomain(ROOT_URL)
            ? UriHelper::getDomain(ROOT_URL)
            : $domain;
    }
}
