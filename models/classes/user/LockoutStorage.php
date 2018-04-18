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

/**
 * Describes interface for user lockouts storage implementations
 * Interface Lockout
 * @package oat\tao\model\user
 */
interface LockoutStorage
{
    /**
     * Returns actual status of the user. Null if no any locks
     * @param $login
     * @return mixed
     */
    public function getStatus($login);

    /**
     * Moves user account to locked state. Also writes who locked the user
     * @param $login
     * @param $by
     * @return mixed
     */
    public function setLockedStatus($login, $by);

    /**
     * Removes all records about user locking
     * @param $login
     * @return mixed
     */
    public function setUnlockedStatus($login);

    /**
     * Returns count of failed login attempts
     * @param $login
     * @return mixed
     */
    public function getFailures($login);

    /**
     * Writes actual value of login failures to user
     * @param $login
     * @param $value
     * @return mixed
     */
    public function setFailures($login, $value);

    /**
     * Returns time when last failed login happened
     * @param $login
     * @return mixed
     */
    public function getLastFailureTime($login);

    /**
     * Returns by who user was blocked
     * @param $login
     * @return mixed
     */
    public function getLockedBy($login);
}
