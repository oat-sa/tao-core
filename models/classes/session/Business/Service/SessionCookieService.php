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

namespace oat\tao\model\session\Business\Service;

use common_http_Request as Request;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\session\Business\Contract\SessionCookieAttributesFactoryInterface;
use oat\tao\model\session\Business\Contract\SessionCookieServiceInterface;
use tao_helpers_Uri as UriHelper;

class SessionCookieService extends InjectionAwareService implements SessionCookieServiceInterface
{
    /** @var SessionCookieAttributesFactoryInterface */
    private $sessionCookieAttributesFactory;
    private bool $flagByArray = false;

    public function __construct(SessionCookieAttributesFactoryInterface $sessionCookieAttributesFactory)
    {
        parent::__construct();

        $this->sessionCookieAttributesFactory = $sessionCookieAttributesFactory;
        if (PHP_VERSION_ID > 70300) {
            $this->flagByArray = true;
        }
        //$this->flagByArray = false;
    }


    public function initializeSessionCookie(): void
    {
        $sessionParams = session_get_cookie_params();
        $cookieDomain  = UriHelper::isValidAsCookieDomain(ROOT_URL)
            ? UriHelper::getDomain(ROOT_URL)
            : $sessionParams['domain'];
        $isSecureFlag  = Request::isHttps();
        $sessionCookieAttributes = $this->sessionCookieAttributesFactory->create();
        if ($this->flagByArray) {
            session_set_cookie_params([
                'lifetime' => $sessionParams['lifetime'],
                'path' => (string)$sessionCookieAttributes,
                'domain' => $cookieDomain,
                'secure' => $isSecureFlag,
                'httponly' => true,
            ]);
        } else {
            session_set_cookie_params(
                $sessionParams['lifetime'],
                (string)$sessionCookieAttributes,
                $cookieDomain,
                $isSecureFlag,
                true
            );
        }

        session_name(GENERIS_SESSION_NAME);

        if (isset($_COOKIE[GENERIS_SESSION_NAME])) {
            // Resume the session
            session_start();

            //cookie keep alive, if lifetime is not 0
            if ($sessionParams['lifetime'] !== 0) {
                $expiryTime = $sessionParams['lifetime'] + time();
                if ($this->flagByArray) {
                    setcookie(GENERIS_SESSION_NAME, session_id(), [
                        'expires' => $expiryTime,
                        'path' => (string)$sessionCookieAttributes,
                        'domain' => $cookieDomain,
                        'secure' => $isSecureFlag,
                        'httponly' => true,
                    ]);
                } else {
                    setcookie(
                        GENERIS_SESSION_NAME,
                        session_id(),
                        $expiryTime,
                        (string)$sessionCookieAttributes,
                        $cookieDomain,
                        $isSecureFlag,
                        true
                    );
                }
            }
        }
    }
}
