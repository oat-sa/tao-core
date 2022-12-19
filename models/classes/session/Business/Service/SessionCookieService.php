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

use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\session\Business\Contract\SessionCookieAttributesFactoryInterface;
use oat\tao\model\session\Business\Contract\SessionCookieServiceInterface;

class SessionCookieService extends InjectionAwareService implements SessionCookieServiceInterface
{
    /** @var SessionCookieAttributesFactoryInterface */
    private $sessionCookieAttributesFactory;

    public function __construct(SessionCookieAttributesFactoryInterface $sessionCookieAttributesFactory)
    {
        parent::__construct();
        $this->sessionCookieAttributesFactory = $sessionCookieAttributesFactory;
    }

    /**
     * initializeSessionCookie
     *
     * @return void
     */
    public function initializeSessionCookie(): void
    {
        $sessionParams = session_get_cookie_params();
        $lifeTime = null;
        if (isset($sessionParams['lifetime'])) {
            $lifeTime = $sessionParams['lifetime'];
        }
        $cookieParams = $this->sessionCookieAttributesFactory->create()->getCookieParams($lifeTime);
        session_set_cookie_params($cookieParams);
        session_name(GENERIS_SESSION_NAME);

        if (isset($_COOKIE[GENERIS_SESSION_NAME])) {
            // Resume the session
            session_start();

            //cookie keep alive, if lifetime is not 0
            if ($sessionParams['lifetime'] !== 0) {
                $expiryTime = $sessionParams['lifetime'] + time();
                setcookie(GENERIS_SESSION_NAME, session_id(), [
                    'expires' => $expiryTime,
                    'domain' => $cookieParams['domain'],
                    'secure' => $cookieParams['secure'],
                    'httponly' => $cookieParams['httponly'],
                ]);
            }
        }
    }
}
