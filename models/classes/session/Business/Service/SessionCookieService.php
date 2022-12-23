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

declare (strict_types=1);

namespace oat\tao\model\session\Business\Service;

use common_http_Request as Request;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\session\Business\Contract\SessionCookieAttributesFactoryInterface;
use oat\tao\model\session\Business\Contract\SessionCookieServiceInterface;
use oat\tao\model\session\Business\Domain\SessionCookieAttribute;
use tao_helpers_Uri as UriHelper;

class SessionCookieService extends InjectionAwareService implements SessionCookieServiceInterface
{
    /** @var SessionCookieAttributesFactoryInterface */
    private $sessionCookieAttributesFactory;
    private $attributes = [];
    private $cookieParams = [];
    private $sessionCookieParams = [];

    public function __construct(SessionCookieAttributesFactoryInterface $sessionCookieAttributesFactory)
    {
        parent::__construct();

        $this->sessionCookieAttributesFactory = $sessionCookieAttributesFactory;
        $iterator = $this->sessionCookieAttributesFactory->create()->getIterator();
        while ($iterator->valid()) {
            $this->attributes[] = $iterator->current();
            $iterator->next();
        }

        $sessionParams = session_get_cookie_params();
        $cookieDomain = UriHelper::isValidAsCookieDomain(ROOT_URL)
            ? UriHelper::getDomain(ROOT_URL)
            : $sessionParams['domain'];
        $isSecureFlag = Request::isHttps();

        if (isset($sessionParams['lifetime'])) {
            $this->attributes[] = new SessionCookieAttribute('lifetime', $sessionParams['lifetime']);
        }

        $this->attributes[] = new SessionCookieAttribute('domain', $cookieDomain);
        $this->attributes[] = new SessionCookieAttribute('secure', $isSecureFlag);
        $this->attributes[] = new SessionCookieAttribute('httponly', true);
        foreach ($this->attributes as $attribute) {
            $this->cookieParams[$attribute->getName()] = $attribute->getValue();
        }
        if ($this->cookieParams['lifetime'] !== 0) {
            $expires = $this->cookieParams['lifetime'] + time();
            $found = false;
            foreach ($this->cookieParams as $key => $value) {
                if ($key === 'lifetime') {
                    $this->sessionCookieParams['expires'] = $expires;
                } else {
                    $this->sessionCookieParams[$key] = $value;
                }
            }
        }
    }

    public function initializeSessionCookie(): void
    {
        session_set_cookie_params($this->cookieParams);
        //temporary line to verify replace is working $tmp = $this->setExpires($params);
        session_name(GENERIS_SESSION_NAME);

        if (isset($_COOKIE[GENERIS_SESSION_NAME])) {
            // Resume the session
            session_start();

            //cookie keep alive, if lifetime is not 0
            if ($this->cookieParams['lifetime'] !== 0) {
                setcookie(
                    GENERIS_SESSION_NAME,
                    session_id(),
                    $this->sessionCookieParams
                );
            }
        }
    }
}
