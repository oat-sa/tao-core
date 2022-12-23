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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\session\Business\Service;

use common_http_Request as Request;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\session\Business\Contract\SessionCookieAttributesFactoryInterface;
use oat\tao\model\session\Business\Contract\SessionCookieServiceInterface;
use oat\tao\model\session\Business\Domain\SessionCookieAttribute;
use oat\tao\model\session\Business\Domain\SessionCookieAttributeCollection;
use tao_helpers_Uri as UriHelper;

class SessionCookieService extends InjectionAwareService implements SessionCookieServiceInterface
{
    private SessionCookieAttributeCollection $cookieAttributeList;

    public function __construct(SessionCookieAttributesFactoryInterface $sessionCookieAttributesFactory)
    {
        parent::__construct();
        $this->enrichCookieParams($sessionCookieAttributesFactory);
    }

    private function enrichCookieParams(SessionCookieAttributesFactoryInterface $sessionCookieAttributesFactory): void
    {
        $this->cookieAttributeList = $sessionCookieAttributesFactory->create();

        $sessionParams = session_get_cookie_params();
        $cookieDomain = UriHelper::isValidAsCookieDomain(ROOT_URL)
            ? UriHelper::getDomain(ROOT_URL)
            : $sessionParams['domain'];
        $isSecureFlag = Request::isHttps();
        if (isset($sessionParams['lifetime'])) {
            $this->cookieAttributeList->add(new SessionCookieAttribute('lifetime', $sessionParams['lifetime']));
        }

        $this->cookieAttributeList->add(new SessionCookieAttribute('domain', $cookieDomain));
        $this->cookieAttributeList->add(new SessionCookieAttribute('secure', $isSecureFlag));
        $this->cookieAttributeList->add(new SessionCookieAttribute('httponly', true));
    }

    private function getCookieParams(): array
    {
        $cookieParams = iterator_to_array($this->cookieAttributeList);
        return array_combine(
            array_map(fn ($attr) => $attr->getName(), $cookieParams),
            array_map(fn ($attr) => $attr->getValue(), $cookieParams),
        );
    }

    private function getSessionCookieParams(): array
    {
        $sessionCookieParams = $this->getCookieParams();
        if ($sessionCookieParams['lifetime'] !== 0) {
            $sessionCookieParams['expires'] = $sessionCookieParams['lifetime'] + time();
            unset($sessionCookieParams['lifetime']);
        }
        return $sessionCookieParams;
    }

    public function initializeSessionCookie(): void
    {
        $cookieParams = $this->getCookieParams();
        session_set_cookie_params($cookieParams);
        //temporary line to verify replace is working $tmp = $this->setExpires($params);
        session_name(GENERIS_SESSION_NAME);

        if (isset($_COOKIE[GENERIS_SESSION_NAME])) {
            // Resume the session
            session_start();

            //cookie keep alive, if lifetime is not 0
            if ($cookieParams['lifetime'] !== 0) {
                setcookie(
                    GENERIS_SESSION_NAME,
                    session_id(),
                    $this->getSessionCookieParams()
                );
            }
        }
    }
}
