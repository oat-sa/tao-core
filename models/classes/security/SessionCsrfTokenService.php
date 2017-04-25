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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */

namespace oat\tao\model\security;

use oat\oatbox\service\ConfigurableService;

/**
 * Class SessionCsrfToken
 *
 * Handles CSRF token through the PHP session
 *
 * @package oat\taoTests\models\runner
 */
class SessionCsrfTokenService extends ConfigurableService implements CsrfToken
{

    use TokenGenerator;

    const SERVICE_ID = 'tao/session-csrf-token';

    const TOKEN_KEY = 'XSRF_TOKEN';


    private $poolSize;

    private $timeLimit;

    /**
     * SessionCsrfToken constructor.
     * @param string $name The token name
     */
    public function __construct(int $poolSize = 10, int $timeLimit = 0)
    {
        $this->poolSize = $poolSize;
        $this->timeLimit = $timeLimit;
    }

    /**
     * Generates and returns the CSRF token
     * @return string
     */
    public function getToken()
    {
        $time = microtime(true);
        $token = $this->generate();

        $session = \PHPSession::singleton();
        $pool = $session->getAttribute(self::TOKEN_KEY);
        if(is_null($pool)){
            $pool = [];
        } else {
            $pool = $this->invalidate($pool);
        }
        $pool[] = [
            $time => $token
        ];

        $session->setAttribute(self::TOKEN_KEY, $pool);
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
        $session = \PHPSession::singleton();
        $pool = $session->getAttribute(self::TOKEN);
        if(!is_null($pool)){
            foreach($pool as $savedToken){
                if($savedToken == $token){
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
        $revoked = false;
        $session = \PHPSession::singleton();
        $pool = $session->getAttribute(self::TOKEN);
        if(!is_null($pool)){
            foreach($pool as $key => $savedToken){
                if($savedToken == $token){
                    unset($pool[$key]);
                    $revoked = true;
                    break;
                }
            }
        }
        $session->setAttribute(self::TOKEN_KEY, $pool);

        return $revoked;
    }

    private function invalidate($pool)
    {
        $actualTime = microtime(true);
        $timeLimit  = $this->timeLimit;


        if($timeLimit > 0){
            $reduced = array_filter($pool, function($time) use($actualTime, $timeLimit){
                            return $timeLimit > 0 && ($time + $timeLimit >= $actualTime);
                        }, ARRAY_FILTER_USE_KEY);
        } else {
            $reduced = $pool;
        }

        if($this->poolSize > 0) {
            asort($reduced);
            while(count($reduced) >= $this->poolSize){
                $reduced = array_shift($reduced);
            }
        }
        return $reduced;
    }
}
