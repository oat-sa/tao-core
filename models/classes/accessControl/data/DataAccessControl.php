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

use common_Logger;
use common_Utils;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\data\permission\PermissionManager;
use oat\oatbox\user\User;
use oat\tao\helpers\ControllerHelper;
use oat\tao\model\accessControl\AccessControl;
use oat\tao\model\controllerMap\ActionNotFoundException;
use oat\tao\model\lock\LockManager;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use tao_helpers_Uri;

/**
 * Interface for data based access control
 */
class DataAccessControl implements AccessControl
{
    private function flattenArray(array $multiDimensionalArray)
    {
        return new RecursiveIteratorIterator(
            new RecursiveArrayIterator($multiDimensionalArray)
        );
    }

    /**
     * @param array $requestParameters
     * @param array $filterNames
     *
     * @return array
     */
    private function extractAndGroupUriFromParameters(array $requestParameters, array $filterNames)
    {
        if (empty($filterNames)) {
            return [];
        }

        $groupedUris = [];

        foreach ($this->flattenArray($requestParameters) as $key => $value) {
            $encodedUri = $this->getEncodedUri($value);

            if (in_array($key, $filterNames, true) && common_Utils::isUri($encodedUri)) {
                $groupedUris[$key][] = $encodedUri;
            }
        }

        return $groupedUris;
    }

    /**
     * @param User $user
     * @param $controller
     * @param $action
     * @param $requestParameters
     *
     * @return bool
     *
     * @see \oat\tao\model\accessControl\AccessControl::hasAccess()
     */
    public function hasAccess(User $user, $controller, $action, $requestParameters)
    {
        $required = [];
        try {
            // $rights = ServiceManager::getServiceManager()->get(RouteAnnotationService::SERVICE_ID)->getRights($controller, $action);
            // todo use $rights when PHPDoc Annotations will be moved to the Doctrines annotations
            $requiredRights = ControllerHelper::getRequiredRights($controller, $action);
            $uris = $this->extractAndGroupUriFromParameters($requestParameters, array_keys($requiredRights));

            foreach ($uris as $name => $urisValue) {
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
     * @param string $decodedUri param to be cleared
     *
     * @return string
     */
    private function getEncodedUri($decodedUri)
    {
        return tao_helpers_Uri::isUriEncoded($decodedUri)
            ? tao_helpers_Uri::decode($decodedUri)
            : $decodedUri;
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
    public function hasPrivileges(User $user, array $required)
    {
        foreach ($required as $resourceId => $right) {
            if ($right === 'WRITE' && !$this->hasWritePrivilege($user, $resourceId)) {
                common_Logger::d('User \'' . $user->getIdentifier() . '\' does not have lock for resource \'' . $resourceId . '\'');
                return false;
            }
            if (!in_array($right, $this->getPermissionProvider()->getSupportedRights())) {
                $required[$resourceId] = PermissionInterface::RIGHT_UNSUPPORTED;
            }
        }
        
        $permissions = $this->getPermissionProvider()->getPermissions($user, array_keys($required));
        foreach ($required as $id => $right) {
            if (!isset($permissions[$id]) || !in_array($right, $permissions[$id])) {
                common_Logger::d('User \'' . $user->getIdentifier() . '\' does not have \'' . $right . '\' permission for resource \'' . $id . '\'');
                return false;
            }
        }
        return true;
    }
    
    private function hasWritePrivilege(User $user, $resourceId)
    {
        $resource = new \core_kernel_classes_Resource($resourceId);
        $lock = LockManager::getImplementation()->getLockData($resource);
        return is_null($lock) || $lock->getOwnerId() == $user->getIdentifier();
    }

    public function getPermissionProvider()
    {
        return PermissionManager::getPermissionModel();
    }
}
