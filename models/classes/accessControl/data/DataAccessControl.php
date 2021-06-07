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
 * Copyright (c) 2014-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\tao\model\accessControl\data;

use common_Logger;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\data\permission\PermissionManager;
use oat\oatbox\user\User;
use oat\tao\helpers\ControllerHelper;
use oat\tao\model\accessControl\AccessControl;
use oat\tao\model\accessControl\filter\ParameterFilterInterface;
use oat\tao\model\accessControl\filter\ParameterFilterProxy;
use oat\tao\model\controllerMap\ActionNotFoundException;
use oat\tao\model\lock\LockManager;

/**
 * Interface for data based access control
 */
class DataAccessControl implements AccessControl
{
    /** @var ParameterFilterInterface */
    private $filter;

    public function setParameterFilter(ParameterFilterInterface $filter): self
    {
        $this->filter = $filter;

        return $this;
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
            $requiredRights = ControllerHelper::getRequiredRights($controller, $action);
            $uris = $this->getParameterFilter()->filter($requestParameters, array_keys($requiredRights));

            foreach ($uris as $name => $urisValue) {
                $required[] = array_fill_keys($urisValue, $requiredRights[$name]);
            }
        } catch (ActionNotFoundException $e) {
            return false;
        }

        return empty($required)
            ? true
            : $this->hasPrivileges($user, array_merge(...$required));
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
     *
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

    private function getParameterFilter(): ParameterFilterInterface
    {
        if (!$this->filter) {
            $this->filter = new ParameterFilterProxy();
        }

        return $this->filter;
    }
}
