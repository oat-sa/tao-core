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

    /**
     * @var PHPSession
     */
    private $session;

    /**
     * Retrieve the pool of tokens
     * @return Token[]
     */
    public function getTokens()
    {
        $pool = [];
        $session = $this->getSession();

        if ($session->hasAttribute(self::TOKEN_KEY)) {
            $pool = $session->getAttribute(self::TOKEN_KEY) ?: [];
        }

        return $pool;
    }

    /**
     * Set the pool of tokens
     * @param Token[] $tokens
     */
    public function setTokens(array $tokens = [])
    {
        $session = $this->getSession();
        $session->setAttribute(self::TOKEN_KEY, $tokens);
    }

    /**
     * Remove all tokens
     */
    public function removeTokens()
    {
        $session = $this->getSession();
        $session->setAttribute(self::TOKEN_KEY, []);
    }

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
}
