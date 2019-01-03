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
use oat\tao\helpers\ControllerHelper;
use common_Logger;
use oat\generis\model\data\permission\PermissionManager;
use oat\oatbox\user\User;
use oat\generis\model\data\permission\PermissionInterface;
use oat\tao\model\lock\LockManager;
use oat\tao\model\controllerMap\ActionNotFoundException;
use common_exception_MissingParameter;

/**
 * Interface for data based access control
 */
class DataAccessControl implements AccessControl
{
    /**
     * (non-PHPdoc)
     * @throws common_exception_MissingParameter
     * @see \oat\tao\model\accessControl\AccessControl::hasAccess()
     */
    public function hasAccess(User $user, $controller, $action, $parameters) {
        $required = array();
        try {
            foreach (ControllerHelper::getRequiredRights($controller, $action) as $paramName => $privileges) {
                if (isset($parameters[$paramName])) {
                    if (is_array($parameters[$paramName])) {
                        foreach ($parameters[$paramName] as $key => $paramVal) {
                            $cleanName = $this->getCleanName($paramName, $paramVal);

                            $required[$cleanName] = $privileges;
                        }
                    } else {
                        $cleanName = $this->getCleanName($paramName, $parameters[$paramName]);

                        $required[$cleanName] = $privileges;
                    }
                } else {
                    throw new common_exception_MissingParameter($paramName);
                }
            }
        } catch (ActionNotFoundException $e) {
            // action not found, no access
            return false;
        }
        
        return empty($required)
            ? true
            : $this->hasPrivileges($user, $required);
    }

    /**
     * Gets the cleaned paramName from paramValue ($cleanName)
     *
     * @param string $paramName just for logging purposes
     * @param string $cleanName param to be cleared
     *
     * @return string
     */
    private function getCleanName($paramName, $cleanName)
    {
        if (preg_match('/^[a-z]*_2_/', $cleanName) != 0) {
            common_Logger::w('url encoded parameter detected for '.$paramName);
            $cleanName = \tao_helpers_Uri::decode($cleanName);
        }

        return $cleanName;
    }
    
    /**
     * Whenever or not the user has the required rights
     * 
     * required takes the form of:
     *   resourceId => $right
     * 
     * @param User $user
     * @param array $required
     * @return boolean
     */
    public function hasPrivileges(User $user, array $required) {
        foreach ($required as $resourceId=>$right) {
            if ($right === 'WRITE' && !$this->hasWritePrivilege($user, $resourceId)) {
                common_Logger::d('User \''.$user->getIdentifier().'\' does not have lock for resource \''.$resourceId.'\'');
                return false;
            }
            if (!in_array($right, $this->getPermissionProvider()->getSupportedRights())) {
                $required[$resourceId] = PermissionInterface::RIGHT_UNSUPPORTED;
            }
        }
        
        $permissions = $this->getPermissionProvider()->getPermissions($user, array_keys($required));
        foreach ($required as $id => $right) {
            if (!isset($permissions[$id]) || !in_array($right, $permissions[$id])) {
                common_Logger::d('User \''.$user->getIdentifier().'\' does not have \''.$right.'\' permission for resource \''.$id.'\'');
                return false;
            }
        }
        return true;
    }
    
    private function hasWritePrivilege(User $user, $resourceId) {
        $resource = new \core_kernel_classes_Resource($resourceId);
        $lock = LockManager::getImplementation()->getLockData($resource);
        return is_null($lock) || $lock->getOwnerId() == $user->getIdentifier();
    }

    public function getPermissionProvider()
    {
        return PermissionManager::getPermissionModel();
    }
}
