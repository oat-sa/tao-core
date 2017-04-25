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
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */

namespace oat\tao\model\security;

/**
 * Interface CsrfToken
 *
 * Provides the API to handle CSRF tokens
 *
 * @package oat\taoTests\models\runner
 */
interface CsrfToken
{
    /**
     * Generates and returns the CSRF token
     * @return string
     */
    public function getToken();

    /**
     * Validates a given token with the current CSRF token
     * @param string $token The given token to validate
     * @return bool
     */
    public function checkToken($token);

    /**
     * Revokes the current CSRF token
     * @return void
     */
    public function revokeToken($token);
}
