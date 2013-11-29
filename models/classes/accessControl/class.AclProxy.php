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
 * Copyright (c) 2013 (original work) Open Assessment Techonologies SA (under the project TAO-PRODUCT);
 * 
 */

/**
 * Proxy for the Acl Implementation
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes_accessControl
 */
class tao_models_classes_accessControl_AclProxy
{
    const CONFIG_KEY_IMPLEMENTATION = 'AclImplementation';
    
    const FALLBACK_IMPLEMENTATION_CLASS = 'tao_models_classes_accessControl_NoAccess';
    
    /**
     * @var tao_models_classes_accessControl_AccessControl
     */
    private static $implementation;

    /**
     * @return tao_models_classes_accessControl_AccessControl
     */
    protected static function getImplementation() {
        if (is_null(self::$implementation)) {
            $implClass = common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->getConfig(self::CONFIG_KEY_IMPLEMENTATION);
            $implClass = !empty($implClass) && class_exists($implClass) ? $implClass : self::FALLBACK_IMPLEMENTATION_CLASS;
            self::$implementation = new $implClass();
        }
        return self::$implementation;
    }
    
    public static function setImplementation(tao_models_classes_accessControl_AccessControl $implementation) {
        self::$implementation = $implementation;
        common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->setConfig(self::CONFIG_KEY_IMPLEMENTATION, get_class($implementation));
    }    
    
    /**
     * Returns whenever or not a user has access to a specified link
     *
     * @param string $extension
     * @param string $controller
     * @param string $action
     * @param array $parameters
     * @return boolean
     */
    public static function hasAccess($extension, $controller, $action = null, $parameters = array()) {
        return self::getImplementation()->hasAccess($extension, $controller, $action, $parameters);
    }
    
    public static function applyRule(tao_models_classes_accessControl_AccessRule $rule) {
        self::getImplementation()->applyRule($rule);
    }
}