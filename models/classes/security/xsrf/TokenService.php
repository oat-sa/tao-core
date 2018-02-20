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

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\TokenGenerator;
use oat\oatbox\service\exception\InvalidService;

/**
 * This service let's you manage tokens to protect against XSRF.
 * The protection works using this workflow :
 *  1. Generate a new token `TokenService::createToken()`
 *  2. Send this token to the client, it will then send it along the HTTP request to protect
 *  3. Verify if the received token is valid `TokenService::checkToken`, and revoke it accordingly
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class TokenService extends ConfigurableService
{

    use TokenGenerator;

    const SERVICE_ID = 'tao/security-xsrf-token';

    //options keys
    const POOL_SIZE_OPT  = 'poolSize';
    const TIME_LIMIT_OPT = 'timeLimit';
    /** @deprecated use TokenService::OPTION_STORE */
    const STORE_OPT    = 'store';
    const OPTION_STORE = 'store';

    const DEFAULT_POOL_SIZE = 10;
    const DEFAULT_TIME_LIMIT = 0;

    /**
     * Create a new TokenService
     *
     * @param array $options the configurations options
     *              - `poolSize` to limit the number of active tokens (0 means unlimited - default to 10)
     *              - `timeLimit` to limit the validity of tokens, in seconds (0 means unlimited - default 0)
     *              - `store` the TokenStore where the tokens are stored
     * @throws InvalidService
     */
    public function __construct($options = [])
    {
        parent::__construct($options);

        if($this->getPoolSize() <= 0 && $this->getTimeLimit() <= 0){
            \common_Logger::w('The pool size and the time limit are both unlimited. Tokens won\'t be invalidated. The store will just grow.');
        }
        $store = $this->getStore();
        if(is_null($store) || !$store instanceof TokenStore){
            throw new InvalidService('The token service requires a TokenStore');
        }
    }

    /**
     * Generates, stores and return a brand new token
     * Triggers the pool invalidation.
     *
     * @return string the token
     */
    public function createToken()
    {
        $time = microtime(true);
        $token = $this->generate();

        $store = $this->getStore();

        $pool = $this->invalidate($store->getTokens());

        $pool[] = [
            'ts' => $time,
            'token' => $token
        ];

        $store->setTokens($pool);

        return $token;
    }

    /**
     * Check if the given token is valid
     * (does not revoke)
     *
     * @param string $token The given token to validate
     * @return boolean
     */
    public function checkToken($token)
    {
        $actualTime = microtime(true);
        $timeLimit  = $this->getTimeLimit();

        $pool = $this->getStore()->getTokens();
        if(!is_null($pool)){

            foreach($pool as $savedToken){
                if($savedToken['token'] == $token){
                    if($timeLimit > 0){
                        return $savedToken['ts'] + $timeLimit > $actualTime;
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Revokes the given token
     * @return true if the revokation succeed (if the token was found)
     */
    public function revokeToken($token)
    {
        $revoked = false;
        $store = $this->getStore();
        $pool = $store->getTokens();
        if(!is_null($pool)){
            foreach($pool as $key => $savedToken){
                if($savedToken['token'] == $token){
                    unset($pool[$key]);
                    $revoked = true;
                    break;
                }
            }
        }
        $store->setTokens($pool);

        return $revoked;
    }

    /**
     * Gets this session's name for token
     * @return {String}
     */
    public function getTokenName()
    {
        $session = \PHPSession::singleton();

        if ($session->hasAttribute(TokenStore::TOKEN_NAME)) {
            $name = $session->getAttribute(TokenStore::TOKEN_NAME);
        } else {
            $name = 'tao_' . substr(md5(microtime()), rand(0, 25), 7);
            $session->setAttribute(TokenStore::TOKEN_NAME, $name);
        }

        return $name;
    }

    /**
     * Invalidate the tokens in the pool :
     *  - remove the oldest if the pool raises it's size limit
     *  - remove the expired tokens
     * @return array the invalidated pool
     */
    protected function invalidate($pool)
    {
        $actualTime = microtime(true);
        $timeLimit  = $this->getTimeLimit();

        $reduced = array_filter($pool, function($token) use($actualTime, $timeLimit){
            if(!isset($token['ts']) || !isset($token['token'])){
                return false;
            }
            if($timeLimit > 0){
                return $token['ts'] + $timeLimit > $actualTime;
            }
            return true;
        });

        if($this->getPoolSize() > 0 && count($reduced) > 0){
            usort($reduced, function($a, $b){
                if($a['ts'] == $b['ts']){
                    return 0;
                }
                return $a['ts'] < $b['ts'] ? -1 : 1;
            });

            //remove the elements at the begining to fit the pool size
            while(count($reduced) >= $this->getPoolSize()){
                array_shift($reduced);
           }
        }
        return $reduced;
    }

    /**
     * Get the configured pool size
     * @return int the pool size, 10 by default
     */
    protected function getPoolSize()
    {
        $poolSize = self::DEFAULT_POOL_SIZE;
        if($this->hasOption(self::POOL_SIZE_OPT)){
            $poolSize = (int)$this->getOption(self::POOL_SIZE_OPT);
        }
        return $poolSize;
    }

    /**
     * Get the configured time limit in seconds
     * @return int the limit
     */
    protected function getTimeLimit()
    {
        $timeLimit = self::DEFAULT_TIME_LIMIT;
        if($this->hasOption(self::TIME_LIMIT_OPT)){
            $timeLimit = (int)$this->getOption(self::TIME_LIMIT_OPT);
        }
        return $timeLimit;
    }

    /**
     * Get the configured store
     * @return TokenStore the store
     */
    protected function getStore()
    {
        $store = null;
        if($this->hasOption(self::STORE_OPT)){
            $store = $this->getOption(self::STORE_OPT);
        }
        return $store;
    }
}
