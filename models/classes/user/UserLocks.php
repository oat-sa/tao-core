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

namespace oat\tao\model\user;

use oat\oatbox\user\User;
use oat\tao\model\event\LoginFailedEvent;
use oat\tao\model\event\LoginSucceedEvent;

/**
 * Interface UserLocks
 * This interface describe how should be implemented service that follows failed user logons
 * and locks them permanently or temporary.
 * @package oat\tao\model\user
 */
interface UserLocks
{
    const SERVICE_ID = 'tao/userlocks';

    /** Use hard lock for failed logon. Be default soft lock will be used */
    const OPTION_USE_HARD_LOCKOUT = 'use_hard_lockout';

    /** Amount of failed login attempts before lockout */
    const OPTION_LOCKOUT_FAILED_ATTEMPTS = 'lockout_failed_attempts';

    /** Duration of soft lock out */
    const OPTION_SOFT_LOCKOUT_PERIOD = 'soft_lockout_period';

    /** List of roles whose users can not be blocked */
    const OPTION_NON_LOCKING_ROLES = 'non_locking_roles';

    /**
     * Event listener that catches failed login events and makes decision to lock user or not
     * @param LoginFailedEvent $event
     * @throws \core_kernel_users_Exception
     */
    public function catchFailedLogin(LoginFailedEvent $event);

    /**
     * Event listener that catches succeed login events and makes decision to unlock user or not
     * @param LoginSucceedEvent $event
     * @throws \core_kernel_users_Exception
     */
    public function catchSucceedLogin(LoginSucceedEvent $event);

    /**
     * Locks user by another user (administrator)
     * @param $user
     * @return mixed
     */
    public function lockUser(User $user);

    /**
     * Unlocks user
     * @param $user
     * @return mixed
     */
    public function unlockUser(User $user);

    /**
     * Returns true if user is locked else false
     * @param $login
     * @return bool
     * @throws \core_kernel_users_Exception
     */
    public function isLocked($login);

    /**
     * Returns true if user can be locked
     * @param $user
     * @return mixed
     */
    public function isLockable(User $user);

    /**
     * Returns remaining time that left before user will be unlocked
     * @param $login
     * @return mixed
     * @throws \core_kernel_users_Exception
     */
    public function getLockoutRemainingTime($login);

    /**
     * Returns remaining attempts that left before user will be locked
     * @param $login
     * @return mixed
     * @throws \core_kernel_users_Exception
     */
    public function getLockoutRemainingAttempts($login);

    /**
     * Returns detailed information about user account status
     * @param $login
     * @return array
     *   boolean        array.locked - returns true if user is locked else false
     *   boolean        array.auto - returns true if user auto locked (locked by himself) else false
     *   string         array.status - human readable string with actual account status
     *   DateInterval   array.remaining - returns valid period of time that left before user will be unlocked, may be null if not applicable
     *   boolean        array.lockable - returns true if user can be locked else false
     * @throws \core_kernel_users_Exception
     */
    public function getStatusDetails($login);

}
