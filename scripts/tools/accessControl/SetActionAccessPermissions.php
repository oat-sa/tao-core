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

namespace oat\tao\scripts\tools\accessControl;

use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\accessControl\ActionAccessControl;

class SetActionAccessPermissions extends ScriptAction
{
    public const OPTION_ACTION = 'action';
    public const OPTION_PERMISSIONS = 'permissions';

    public const ACTION_ADD = 'add';
    public const ACTION_REMOVE = 'remove';

    /**
     * @example [
     *     '--action', 'add',
     *     '--permissions', [
     *         'controller' => [
     *             'action' => [
     *                 'role1' => 'READ',
     *                 'role2' => 'WRITE',
     *             ],
     *         ],
     *     ],
     * ]
     */
    protected function provideOptions()
    {
        return [
            self::OPTION_ACTION => [
                'prefix' => 'a',
                'longPrefix' => self::OPTION_ACTION,
                'defaultValue' => self::ACTION_ADD,
                'description' => sprintf(
                    'Required action to apply changes on permissions list: %s, %s',
                    self::ACTION_ADD,
                    self::ACTION_REMOVE
                ),
            ],
            self::OPTION_PERMISSIONS => [
                'prefix' => 'p',
                'longPrefix' => self::OPTION_PERMISSIONS,
                'cast' => 'array',
                'defaultValue' => [],
                'description' => 'List of permissions to add/remove.',
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'Allow to add/remove list of permissions to a specific action and role.';
    }

    protected function run()
    {
        $serviceManager = $this->getServiceManager();
        $action = $this->getOption(self::OPTION_ACTION);

        /** @var ActionAccessControl $actionAccessControl */
        $actionAccessControl = $serviceManager->get(ActionAccessControl::SERVICE_ID);

        if ($action === self::ACTION_ADD) {
            $actionAccessControl->addPermissions($this->getOption(self::OPTION_PERMISSIONS));
        } elseif ($action === self::ACTION_REMOVE) {
            $actionAccessControl->removePermissions($this->getOption(self::OPTION_PERMISSIONS));
        }

        $serviceManager->register(ActionAccessControl::SERVICE_ID, $actionAccessControl);
    }
}
