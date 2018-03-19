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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\user\implementation;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\tao\model\event\LoginFailedEvent;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\user\UserLocks;

/**
 * Class NoUserLocksService
 * @package oat\tao\model\user
 */
class NoUserLocksService extends ConfigurableService implements UserLocks
{
    /**
     * @param LoginFailedEvent $event
     */
    public function catchFailedLogin(LoginFailedEvent $event)
    {
        // do nothing
    }

    /**
     * @param LoginSucceedEvent $event
     */
    public function catchSucceedLogin(LoginSucceedEvent $event)
    {
        // do nothing
    }

    /**
     * @param $user
     * @return bool
     */
    public function lockUser(User $user)
    {
        return true;
    }

    /**
     * @param $user
     * @return bool
     */
    public function unlockUser(User $user)
    {
        return true;
    }

    /**
     * @param $login
     * @return bool
     * @throws \Exception
     */
    public function isLocked($login)
    {
        return false;
    }

    /**
     * @param $user
     * @return bool|mixed
     */
    public function isLockable(User $user)
    {
        return false;
    }

    /**
     * @param $login
     * @return bool|int|mixed
     */
    public function getLockoutRemainingAttempts($login)
    {
        return false;
    }

    /**
     * @param $login
     * @return array
     * @throws \Exception
     */
    public function getStatusDetails($login)
    {
        return [
            'locked' => false,
            'auto' => false,
            'status' => __('enabled'),
            'remaining' => null,
            'lockable' => false
        ];
    }

    /**
     * @param $login
     * @return mixed
     */
    public function getLockoutRemainingTime($login)
    {
        return false;
    }
}
