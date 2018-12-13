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

use common_exception_MissingParameter;
use common_Logger;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\data\permission\PermissionManager;
use oat\oatbox\user\User;
use oat\tao\helpers\ControllerHelper;
use oat\tao\model\accessControl\AccessControl;
use oat\tao\model\controllerMap\ActionNotFoundException;
use oat\tao\model\lock\LockManager;

/**
 * Interface for data based access control
 */
class DataAccessControl implements AccessControl
{
    private function getUris(array $parameters, array $filterNames)
    {
        $uris = [];

        foreach ($parameters as $name => $uri) {
            if (is_array($uri)) {
                $uris = array_merge_recursive($uris, $this->getUris($uri, $filterNames));
            } else {
                $encodedUri = $this->getEncodedUri($uri);

                if (in_array($name, $filterNames, true) && \common_Utils::isUri($encodedUri)) {
                    $uris[$name][] = $encodedUri;
                }
            }
        }

        return $uris;
    }

    /**
     * (non-PHPdoc)
     * @throws common_exception_MissingParameter
     * @see \oat\tao\model\accessControl\AccessControl::hasAccess()
     */
    public function hasAccess(User $user, $controller, $action, $parameters) {
        $required = array();
        try {
            $requiredRights = ControllerHelper::getRequiredRights($controller, $action);
            $uris = $this->getUris($parameters, array_keys($requiredRights));

            foreach($uris as $name => $urisValue) {
                $required[] = array_fill_keys($urisValue, $requiredRights[$name]);
            }
        } catch (ActionNotFoundException $e) {
            // action not found, no access
            return false;
        }
        
        return empty($required)
            ? true
            : $this->hasPrivileges($user, array_merge(...$required));
    }

    /**
     * Gets the cleaned paramName from paramValue ($cleanName)
     *
     * @param string $paramName just for logging purposes
     * @param string $decodedUri param to be cleared
     *
     * @return string
     */
    private function getEncodedUri($decodedUri)
    {
        if (preg_match('/^[a-z]*_2_/', $decodedUri) === 1) {
            $decodedUri = \tao_helpers_Uri::decode($decodedUri);
        }

        return $decodedUri;
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
