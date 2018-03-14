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

    const OPTION_LOCKOUT_STORAGE = 'lockout_storage';

    /** Use hard lock for failed logon. Be default soft lock will be used */
    const OPTION_USE_HARD_LOCKOUT = 'use_hard_lockout';

    /** Amount of failed login attempts before lockout */
    const OPTION_LOCKOUT_FAILED_ATTEMPTS = 'lockout_failed_attempts';

    /** Duration of soft lock out */
    const OPTION_SOFT_LOCKOUT_PERIOD = 'soft_lockout_period';

    /**
     * Event listener that catches failed login events and makes decision to lock user or not
     * @param LoginFailedEvent $event
     */
    public function catchFailedLogin(LoginFailedEvent $event);

    /**
     * Event listener that catches succeed login events and makes decision to unlock user or not
     * @param LoginSucceedEvent $event
     */
    public function catchSucceedLogin(LoginSucceedEvent $event);

    /**
     * Locks user by another user (administrator)
     * @param $user
     * @return mixed
     */
    public function lockUser($user);

    /**
     * Unlocks user
     * @param $user
     * @return mixed
     */
    public function unlockUser($user);

    /**
     * Returns true if user is locked else false
     * @param $login
     * @return bool
     */
    public function isLocked($login);

    /**
     * Returns remaining time that left before user will be unlocked
     * @param $login
     * @return mixed
     */
    public function getLockoutRemainingTime($login);

    /**
     * Returns remaining attempts that left before user will be locked
     * @param $login
     * @return mixed
     */
    public function getLockoutRemainingAttempts($login);

    /**
     * Returns detailed information about user account status
     * @param $login
     * @return array
     */
    public function getStatusDetails($login);

}
