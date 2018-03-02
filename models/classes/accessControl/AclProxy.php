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

use oat\tao\model\accessControl\func\AclProxy as FuncProxy;
use oat\tao\model\accessControl\data\DataAccessControl;
use oat\oatbox\user\User;

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
     * @var array
     */
    private static $implementations;

    /**
     * Get the current access control implementations.
     *
     * @return array
     */
    protected static function getImplementations()
    {
        if (is_null(self::$implementations)) {
            self::$implementations = array(
                new FuncProxy(),
                new DataAccessControl()
            );

        }

        return self::$implementations;
    }

    /**
     * Returns whenever or not a user has access to a specified link
     *
     * @param User $user
     * @param string $controller
     * @param string $action
     * @param array $parameters
     *
     * @return boolean
     */
    public static function hasAccess(User $user, $controller, $action, $parameters)
    {
        foreach (self::getImplementations() as $impl) {
            if (!$impl->hasAccess($user, $controller, $action, $parameters)) {
                return false;
            }
        }

        return true;
    }
}
