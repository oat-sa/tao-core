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
 */

declare(strict_types=1);

namespace oat\tao\model\accessControl\data;

use oat\oatbox\user\User;
use Psr\Log\LoggerInterface;
use oat\tao\model\lock\LockManager;
use oat\tao\helpers\ControllerHelper;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\log\logger\AdvancedLogger;
use oat\tao\model\accessControl\AccessControl;
use oat\generis\model\data\permission\PermissionManager;
use oat\tao\model\controllerMap\ActionNotFoundException;
use oat\generis\model\data\permission\PermissionInterface;
use oat\oatbox\log\logger\extender\ContextExtenderInterface;
use oat\tao\model\accessControl\filter\ParameterFilterProxy;
use oat\tao\model\accessControl\filter\ParameterFilterInterface;

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
     * {@inheritdoc}
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
        } catch (ActionNotFoundException $exception) {
            $this->getAdvancedLogger()->error(
                $exception->getMessage(),
                [ContextExtenderInterface::CONTEXT_EXCEPTION => $exception]
            );

            return false;
        }

        return empty($required) || $this->hasPrivileges($user, array_merge(...$required));
    }

    /**
     * Whenever or not the user has the required rights
     *
     * required takes the form of:
     *   resourceId => $right
     *
     * @return bool
     */
    public function hasPrivileges(User $user, array $required)
    {
        foreach ($required as $resourceId => $right) {
            if ($right === 'WRITE' && !$this->hasWritePrivilege($user, $resourceId)) {
                $this->getAdvancedLogger()->info(
                    'User does not have lock for resource.',
                    [
                        'resourceId' => $resourceId,
                    ]
                );

                return false;
            }

            if (!in_array($right, $this->getPermissionProvider()->getSupportedRights())) {
                $required[$resourceId] = PermissionInterface::RIGHT_UNSUPPORTED;
            }
        }

        $permissions = $this->getPermissionProvider()->getPermissions($user, array_keys($required));

        foreach ($required as $id => $right) {
            if (!isset($permissions[$id]) || !in_array($right, $permissions[$id], true)) {
                $this->getAdvancedLogger()->info(
                    'User does not have required permission for resource.',
                    [
                        'requiredPermission' => $right,
                        'resourceId' => $id,
                        'resourcePermissions' => $permissions[$id] ?? [],
                    ]
                );

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

    private function getAdvancedLogger(): LoggerInterface
    {
        return ServiceManager::getServiceManager()->getContainer()->get(AdvancedLogger::ACL_SERVICE_ID);
    }
}
