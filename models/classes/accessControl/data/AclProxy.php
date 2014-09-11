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
namespace oat\tao\model\accessControl\data;

use oat\tao\model\accessControl\AccessControl;
use oat\tao\model\accessControl\data\DataAccessControl;
use common_ext_ExtensionsManager;
use common_Logger;
use oat\tao\helpers\ControllerHelper;

/**
 * Proxy for the Acl Implementation
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 
 */
class AclProxy implements AccessControl
{
    const CONFIG_KEY_IMPLEMENTATION = 'DataAccessControl';
    
    const FALLBACK_IMPLEMENTATION_CLASS = 'oat\tao\model\accessControl\data\implementation\FreeAccess';
    
    /**
     * @var DataAccessControl
     */
    private static $implementation;

    /**
     * @return DataAccessControl
     */
    protected static function getImplementation() {
        if (is_null(self::$implementation)) {
            $implClass = common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->getConfig(self::CONFIG_KEY_IMPLEMENTATION);
            if (empty($implClass) || !class_exists($implClass)) {
                common_Logger::e('No implementation found for Access Control, locking down the server');
                $implClass = self::FALLBACK_IMPLEMENTATION_CLASS;
            }
            self::$implementation = new $implClass();
        }
        return self::$implementation;
    }
    
    /**
     * Change the implementation of the access control permanently
     * 
     * @param DataAccessControl $implementation
     */
    public static function setImplementation(DataAccessControl $implementation) {
        self::$implementation = $implementation;
        common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->setConfig(self::CONFIG_KEY_IMPLEMENTATION, get_class($implementation));
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
    public function hasAccess($user, $controller, $action, $parameters) {
        $required = array();
        foreach (self::getRequiredPrivileges($controller, $action) as $paramName => $privileges) {
            if (isset($parameters[$paramName])) {
                if (substr($parameters[$paramName], 0, 7) == 'http_2_') {
                    \common_Logger::w('url encoded parameter detected for '.$paramName);
                    $cleanName = \tao_helpers_Uri::decode($parameters[$paramName]);
                } else {
                    $cleanName = $parameters[$paramName];
                }
                
                $required[$cleanName] = $privileges;
            } else {
                throw new \Exception('Missing parameter ' . $paramName . ' for ' . $controller . '/' . $action);
            }
        }
        if (!empty($required)) {
            $privileges = self::getImplementation()->getPrivileges($user, array_keys($required));
        }
        
        foreach ($required as $id => $reqPriv) {
            if (!in_array($reqPriv, $privileges[$id])) {
                common_Logger::d('Missing '.$reqPriv.' for resource '.$id);
                return false;
            }
        }
        return true;
    }
    
    public static function getRequiredPrivileges($controllerClassName, $actionName) {
        $desc = ControllerHelper::getActionDescription($controllerClassName, $actionName);
        return $desc->getRequiredPrivileges();
    }
    
    public static function getExistingPrivileges() {
        return array(
            'WRITE',
            'GRANT'
        );
    }
    
    public static function getPrivilegeLabels() {
        return array(
            'WRITE' => __('Access'),
            'GRANT' => __('Manage Access')
        );
    }
}
