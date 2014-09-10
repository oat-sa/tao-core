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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
namespace oat\tao\model\accessControl\data;

use oat\tao\model\accessControl\AccessControl;
use oat\generis\model\data\permission\PermissionManager;
use oat\tao\helpers\ControllerHelper;
use common_Logger;
use oat\generis\model\data\permission\PermissionInterface;

/**
 * Interface for data based access control
 */
class DataAccessControl implements AccessControl
{
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\accessControl\AccessControl::hasAccess()
     */
    public function hasAccess($user, $controller, $action, $parameters) {
        $required = array();
        foreach ($this->getRequiredRights($controller, $action) as $paramName => $privileges) {
            if (isset($parameters[$paramName])) {
                if (substr($parameters[$paramName], 0, 7) == 'http_2_') {
                    common_Logger::w('url encoded parameter detected for '.$paramName);
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
            $permissions = PermissionManager::getPermissionModel()->getPermissions($user, array_keys($required));
            foreach ($required as $id => $right) {
                if (!isset($permissions[$id]) || !in_array($right, $permissions[$id])) {
                    common_Logger::d('User \''.$user.'\' does not have \''.$right.'\' permission for resource \''.$id.'\'');
                    return false;
                }
            }
        }
    
        return true;
    }
    
    /**
     * Get the required rights for the execution of an action
     * 
     * Returns an associative array with the parameter as key
     * and the rights as values
     * 
     * @param string $controllerClassName
     * @param string $actionName
     * @return array
     */
    public function getRequiredRights($controllerClassName, $actionName) {
        $rights = array();
        $controller = ControllerHelper::getActionDescription($controllerClassName, $actionName);
        foreach ($controller->getRequiredRights() as $paramName => $right) {
            $rights[$paramName] = in_array($right, PermissionManager::getPermissionModel()->getSupportedRights())
                ? $right : PermissionInterface::RIGHT_UNSUPPORTED;
        }
        return $rights;
    }
}