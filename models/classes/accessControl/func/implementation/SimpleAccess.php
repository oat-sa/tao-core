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
use oat\tao\model\accessControl\func\AccessRule;
use common_ext_ExtensionsManager;
use common_session_SessionManager;

/**
 * Simple ACL Implementation deciding whenever or not to allow access
 * strictly by the BASEUSER role and a whitelist
 * 
 * Not to be used in production, since testtakers cann access the backoffice
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 
 */
class SimpleAccess
    implements FuncAccessControl
{
    
    const WHITELIST_KEY = 'SimpleAclWhitelist';
    
    private $controllers = array();
    
    private $fuzzyMatches  = array();
    
    /**
     * 
     */
    public function __construct() {
        $data = common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->getConfig(self::WHITELIST_KEY);
        if (is_array($data)) {
            $this->controllers = $data['controllers'];
            $this->controllers = $data['fuzzy'];
        }
    }

    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\accessControl\func\FuncAccessControl::accessPossible()
     */
    public function accessPossible($user, $action) {
        $isUser = false;
        foreach (common_session_SessionManager::getSession()->getUserRoles() as $role) {
            if ($role == INSTANCE_ROLE_BASEUSER) {
                $isUser = true;
                break;
            }
        }
        return $isUser || $this->inWhiteList($action);
    }
    
    public function applyRule(AccessRule $rule) {
        if ($rule->getRole()->getUri() == INSTANCE_ROLE_ANONYMOUS) {
            $mask = $rule->getMask();
            // if legacy
            $controllerMask = $mask['ext'].'_actions_'.(isset($mask['mod']) ? $mask['mod'] : '*');
            $action = isset($mask['act']) ? $mask['act'] : '*';
            
            if (strpos($controllerMask, '*') !== false) {
                // fuzzy match
                
            } else {
                // exact match
                
            }
            $this->whitelist($controllerMask, $action);
        }
    }
    
    public function revokeRule(AccessRule $rule) {
        if ($rule->getRole()->getUri() == INSTANCE_ROLE_ANONYMOUS) {
            $mask = $rule->getMask();
            $ruleString = $mask['ext'].'::'.(isset($mask['mod']) ? $mask['mod'] : '*').'::'.(isset($mask['act']) ? $mask['act'] : '*');
            $remaining = array_diff(explode(',', $this->whitelist), array($ruleString));
            $this->whitelist = implode(',', $remaining);
            $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
            common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->setConfig(self::WHITELIST_KEY, $this->whitelist);
        }
    }
    
    private function inWhiteList($actionId) {
        list($controller, $action) = explode('@', $actionId);
        return false;
        if (isset($this->whitelist[$controller])) {
            return $this->whitelist[$controller] == '*' || in_array($action, $this->whitelist[$controller]);
        }
        if (false) {
            $fuzz;
        }
        foreach ($this->whitelist as $key => $value) {
            
        }
        return strpos($this->whitelist, $extension.'::'.$controller.'::'.$action) !== false
            || strpos($this->whitelist, $extension.'::'.$controller.'::*') !== false
            || strpos($this->whitelist, $extension.'::*::*') !== false;
    }
    
    private function whiteListExact($controller, $action) {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        
    }
    
    private function whiteListFuzzy($controllerMask, $action) {
        
    }
    
    
    private function whiteList($controllerMask, $action) {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $entry = $extension.'::'.(is_null($controller) ? '*' : $controller).'::'.(is_null($action) ? '*' : $action);
        $this->whitelist = $ext->hasConfig(self::WHITELIST_KEY) ? $ext->getConfig(self::WHITELIST_KEY) : array();
        $this->whitelist .= (empty($this->whitelist) ? '' : ',').$entry;
        common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->setConfig(self::WHITELIST_KEY, $this->whitelist);
    }
}