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

namespace oat\tao\model\security\xsrf;

use oat\oatbox\Configurable;
use PHPSession;

/**
 * TokenStore into the PHP session
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class TokenStoreSession extends Configurable implements TokenStore
{
    private const TOKEN_NAMESPACE = 'CSRF_TOKEN_';

    /**
     * @var PHPSession
     */
    private $session;

    /**
     * @return PHPSession
     */
    private function getSession()
    {
        if ($this->session === null) {
            $this->session = PHPSession::singleton();
        }
        return $this->session;
    }

    public function getToken(string $tokenId): ?Token
    {
        return $this->hasToken($tokenId)
            ? $this->getSession()->getAttribute(self::TOKEN_NAMESPACE . $tokenId)
            : null;
    }

    public function setToken(string $tokenId, Token $token): void
    {
        $this->getSession()->setAttribute(self::TOKEN_NAMESPACE . $tokenId, $token);
    }

    public function hasToken(string $tokenId): bool
    {
        return $this->getSession()->hasAttribute(self::TOKEN_NAMESPACE . $tokenId);
    }

    public function removeToken(string $tokenId): bool
    {
        $removed = false;
        if ($this->hasToken($tokenId)) {
            $this->getSession()->removeAttribute(self::TOKEN_NAMESPACE . $tokenId);
            $removed = true;
        }

        return $removed;
    }

    public function clear(): void
    {
        foreach($this->getSession()->getAttributeNames() as $key) {
            if (strpos($key, self::TOKEN_NAMESPACE) === 0) {
                $this->getSession()->removeAttribute($key);
            }
        }
    }

    public function getAll(): array
    {
        $tokens = [];
        foreach ($this->getSession()->getAttributeNames() as $sessionAttributeKey) {
            if (strpos($sessionAttributeKey, self::TOKEN_NAMESPACE) === 0) {
                $tokens[] = $this->getSession()->getAttribute($sessionAttributeKey);
            }
        }
        return $tokens;
    }
}
