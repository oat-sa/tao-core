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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\accessControl;

use oat\oatbox\user\User;
use oat\oatbox\service\ConfigurableService;
use common_session_SessionManager as SessionManager;

class ActionAccessControl extends ConfigurableService
{
    public const SERVICE_ID = 'tao/ActionAccessControl';

    /**
     * @example [
     *     'controller' => [
     *         'action1' => [
     *             'role1' => 'READ',
     *             'role2' => 'WRITE,
     *         ],
     *     ],
     * ]
     */
    public const OPTION_PERMISSIONS = 'permissions';

    public const DENY = 'DENY';
    public const READ = 'READ';
    public const WRITE = 'WRITE';
    public const GRANT = 'GRANT';

    /**
     * @example $permissionsToAdd = [
     *     'controller' => [
     *         'action1' => [
     *             'role1' => 'READ',
     *             'role2' => 'WRITE,
     *         ],
     *     ],
     * ]
     */
    public function addPermissions(array $permissionsToAdd = []): void
    {
        $permissions = $this->getOption(self::OPTION_PERMISSIONS, []);

        foreach ($permissionsToAdd as $controller => $actions) {
            if (empty($permissions[$controller])) {
                $permissions[$controller] = $actions;

                continue;
            }

            foreach ($actions as $action => $rolePermissions) {
                if (empty($permissions[$controller][$action])) {
                    $permissions[$controller][$action] = $rolePermissions;

                    continue;
                }

                $actionPermissions = $permissions[$controller][$action] ?? [];
                $permissions[$controller][$action] = array_merge($actionPermissions, $rolePermissions);
            }
        }

        $this->setOption(self::OPTION_PERMISSIONS, $permissions);
    }

    /**
     * @example $permissionsToRemove = [
     *     'controller' => [
     *         'action1' => [
     *             'role1',
     *             'role2',
     *         ],
     *     ],
     * ]
     */
    public function removePermissions(array $permissionsToRemove = []): void
    {
        $permissions = $this->getOption(self::OPTION_PERMISSIONS, []);

        foreach ($permissionsToRemove as $controller => $actions) {
            foreach ($actions as $action => $roles) {
                foreach ($roles as $role => $rolePermissions) {
                    $index = !is_numeric($role) ? $role : $rolePermissions;
                    unset($permissions[$controller][$action][$index]);
                }

                if (empty($permissions[$controller][$action])) {
                    unset($permissions[$controller][$action]);
                }
            }

            if (empty($permissions[$controller])) {
                unset($permissions[$controller]);
            }
        }

        $this->setOption(self::OPTION_PERMISSIONS, $permissions);
    }

    public function hasReadAccess(string $controller, string $action, ?User $user = null): bool
    {
        return $this->hasAccess([self::READ, self::WRITE, self::GRANT], $controller, $action, $user);
    }

    public function hasWriteAccess(string $controller, string $action, ?User $user = null): bool
    {
        return $this->hasAccess([self::WRITE, self::GRANT], $controller, $action, $user);
    }

    public function hasGrantAccess(string $controller, string $action, ?User $user = null): bool
    {
        return $this->hasAccess([self::GRANT], $controller, $action, $user);
    }

    private function hasAccess(array $allowedPermissions, string $controller, string $action, ?User $user = null): bool
    {
        $roleIsListed = false;
        $userRoles = $this->getUserRoles($user);
        $permissions = $this->getPermissions($controller, $action);

        foreach ($permissions as $role => $permission) {
            if (in_array($role, $userRoles, true)) {
                if (in_array($permission, $allowedPermissions, true)) {
                    return true;
                }

                $roleIsListed = true;
            }
        }

        if ($roleIsListed) {
            $this->logWarning(
                sprintf(
                    'User roles "%s" permissions "%s" do not have allowed permissions "%s" for controller "%s::%s',
                    implode(', ', $userRoles),
                    implode(', ', $permissions),
                    implode(', ', $allowedPermissions),
                    $controller,
                    $action
                )
            );
        }

        return !$roleIsListed;
    }

    private function getPermissions(?string $controller = null, ?string $action = null): array
    {
        $permissions = $this->getOption(self::OPTION_PERMISSIONS, []);

        if (!empty($permissions) && $controller !== null) {
            $permissions = $permissions[$controller] ?? [];

            if ($action !== null) {
                $permissions = $permissions[$action] ?? [];
            }
        }

        return $permissions;
    }

    private function getUserRoles(?User $user = null): array
    {
        return ($user ?? $this->getUser())->getRoles();
    }

    private function getUser(): User
    {
        return SessionManager::getSession()->getUser();
    }
}
