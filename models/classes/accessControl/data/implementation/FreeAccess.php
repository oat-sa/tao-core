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

namespace oat\tao\model\accessControl\data\implementation;

use oat\tao\model\accessControl\DataAccessControl;

/**
 * Sample data access control implementation giving free
 * access to all resources.
 * 
 * does not require privileges
 * does not grant privileges
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 */
class FreeAccess
    implements DataAccessControl
{
    /**
     * 
     */
    public function __construct() {
    }
    
    public function getRequiredPrivileges($action) {
        return action();
    }
    
    public function getPrivileges($user, $resourceId) {
        return action();
    }
}