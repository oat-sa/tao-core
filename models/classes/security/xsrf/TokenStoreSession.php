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

/**
 * TokenStore into the PHP session
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class TokenStoreSession extends Configurable implements TokenStore
{
    /**
     * Retrieve the pool of tokens
     * @return array the tokens
     */
    public function getTokens()
    {
        $pool = null;
        $session = \PHPSession::singleton();
        if($session->hasAttribute(self::TOKEN_KEY)){
            $pool = $session->getAttribute(self::TOKEN_KEY);
        }
        if(is_null($pool)){
            $pool = [];
        }

        return $pool;
    }

    /**
     * Set the pool of tokens
     * @param array $tokens the poll
     */
    public function setTokens(array $tokens = [])
    {
        $session = \PHPSession::singleton();
        $session->setAttribute(self::TOKEN_KEY, $tokens);
    }

    /**
     * Remove all tokens
     */
    public function removeTokens()
    {
        $session = \PHPSession::singleton();
        $session->setAttribute(self::TOKEN_KEY, []);
    }
}
