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

namespace oat\tao\model\accessControl\func\implementation;

use oat\tao\model\accessControl\func\FuncAccessControl;
/**
 * Sample ACL Implementation allowing access to everything
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 
 */
class Wrapper implements FuncAccessControl {
    
    public function getRealClass() {
        return new \funcAcl_models_classes_FuncAcl();
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\accessControl\func\FuncAccessControl::accessPossible()
     */
    public function accessPossible($user, $action) {
        return false;
    }

    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\accessControl\func\FuncAccessControl::applyRule()
     */
    public function applyRule(tao_models_classes_accessControl_AccessRule $rule) {
        // nothing can be done
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\accessControl\func\FuncAccessControl::revokeRule()
     */
    public function revokeRule(tao_models_classes_accessControl_AccessRule $rule) {
        // nothing can be done
    }
}