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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */

namespace oat\tao\model\accessControl;

use oat\tao\model\accessControl\data\AclProxy as DataProxy;
use oat\tao\model\accessControl\func\AclProxy as FuncProxy;

/**
 * Proxy for the Acl Implementation
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 
 */
class AclProxy
{
    /**
     * @var DataAccessControl
     */
    private static $implementations;

    /**
     * @return DataAccessControl
     */
    protected static function getImplementations() {
        if (is_null(self::$implementations)) {
            self::$implementations = array(
            	new FuncProxy()
                //,new DataProxy()
            );
        }
        return self::$implementations;
    }
    
    /**
     * Returns whenever or not a user has access to a specified link
     *
     * @param string $action
     * @param string $controller
     * @param string $extension
     * @param array $parameters
     * @return boolean
     */
    public static function hasAccess($user, $action, $parameters) {
        $access = true;
        foreach (self::getImplementations() as $impl) {
            $access = $access && $impl->hasAccess($user, $action, $parameters);
        }
        return $access;
    }
}