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
 */
class TokenService extends ConfigurableService
{

    use TokenGenerator;

    const SERVICE_ID = 'tao/security-xsrf-token';

    const POOL_SIZE_OPT = 'poolSize';
    const TIME_LIMIT_OPT = 'timeLimit';
    const STORE_OPT = 'store';

    /**
     */
    public function __construct($options = [])
    {
        parent::__construct($options);

        if($this->getPoolSize() <= 0 && $this->getTimeLimit() <= 0){
            \common_Logger::w('the pool size and the time limit are both unlimited. Tokens won\'t be invalidated. The store will just grow');
        }
        $store = $this->getStore();
        if(is_null($store) || !$store instanceof TokenStore){
            throw new InvalidService('The token service requires a TokenStore');
        }
    }

    /**
     * Generates and returns the CSRF token
     * @return string
     */
    public function createToken()
    {
        $time = microtime(true);
        $token = $this->generate();

        $store = $this->getStore();

        $pool = $this->invalidate($store->getTokens());

        \common_Logger::w('CREATE TOKEN ' . $token);

        $pool[] = [
            'ts' => $time,
            'token' => $token
        ];

        $store->setTokens($pool);

        return $token;
    }

    /**
     * Validates a given token with the current CSRF token
     * @param string $token The given token to validate
     * @param int $lifetime A max life time for the current token, default to infinite
     * @return bool
     */
    public function checkToken($token)
    {
        \common_Logger::d('CHECK TOKEN ' . $token);

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
     * Revokes the current CSRF token
     * @return void
     */
    public function revokeToken($token)
    {
        \common_Logger::d('REVOKE TOKEN ' . $token);
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

            while(count($reduced) >= $this->getPoolSize()){
                array_shift($reduced);
            }
        }
        return $reduced;
    }

    protected function getPoolSize()
    {
        $poolSize = 10;
        if($this->hasOption(self::POOL_SIZE_OPT)){
            $poolSize = (int)$this->getOption(self::POOL_SIZE_OPT);
        }
        return $poolSize;
    }

    protected function getTimeLimit()
    {
        $timeLimit = 0;
        if($this->hasOption(self::TIME_LIMIT_OPT)){
            $timeLimit = (int)$this->getOption(self::TIME_LIMIT_OPT);
        }
        return $timeLimit;
    }

    protected function getStore()
    {
        $store = null;
        if($this->hasOption(self::STORE_OPT)){
            $store = $this->getOption(self::STORE_OPT);
        }
        return $store;
    }
}
