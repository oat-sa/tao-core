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

use DateInterval;
use DateTime;
use DateTimeImmutable;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\tao\helpers\UserHelper;
use oat\tao\model\event\LoginFailedEvent;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\LockoutStorage;
use oat\tao\model\user\UserLocks;
use tao_helpers_Date;
use tao_models_classes_UserService;

/**
 * Class UserLocksService
 * @package oat\tao\model\user
 */
class UserLocksService extends ConfigurableService implements UserLocks
{
    /** Which storage implementation to use for user lockouts */
    const OPTION_LOCKOUT_STORAGE = 'lockout_storage';

    /** @var LockoutStorage */
    private $lockout;

    public function setHardLockout()
    {
        $this->setOption(self::OPTION_USE_HARD_LOCKOUT, true);
    }

    public function setSoftLockout()
    {
        $this->setOption(self::OPTION_USE_HARD_LOCKOUT, false);
    }

    public function setFailedAttemptsBeforeLockout($attempts = 6)
    {
        $this->setOption(self::OPTION_LOCKOUT_FAILED_ATTEMPTS, $attempts);
    }

    public function setPeriodOfSoftLockout($period = 'PT30M')
    {
        $this->setOption(self::OPTION_SOFT_LOCKOUT_PERIOD, $period);
    }

    public function setLockStorage($implementation = RdfLockoutStorage::class)
    {
        $this->setOption(self::OPTION_LOCKOUT_STORAGE, $implementation);
    }

    public function setNonLockingRoles(array $roles)
    {
        $this->setOption(self::OPTION_NON_LOCKING_ROLES, $roles);
    }

    /**
     * Returns proper lockout implementation
     * @return LockoutStorage|RdfLockoutStorage
     */
    protected function getLockout()
    {
        if (!$this->lockout || !$this->lockout instanceof LockoutStorage) {
            $lockout = $this->getOption(self::OPTION_LOCKOUT_STORAGE);
            $this->lockout = ($lockout and class_exists($lockout)) ? new $lockout : new RdfLockoutStorage();
        }

        return $this->lockout;
    }

    /**
     * @param LoginFailedEvent $event
     * @throws \core_kernel_users_Exception
     */
    public function catchFailedLogin(LoginFailedEvent $event)
    {
        $this->increaseLoginFails($event->getLogin());
    }

    /**
     * @param LoginSucceedEvent $event
     * @throws \core_kernel_users_Exception
     */
    public function catchSucceedLogin(LoginSucceedEvent $event)
    {
        $this->unlockUser(UserHelper::getUser($this->getLockout()->getUser($event->getLogin())));
    }

    /**
     * @param string $login
     * @throws \core_kernel_users_Exception
     * @throws \Exception
     */
    private function increaseLoginFails($login)
    {
        $user = UserHelper::getUser($this->getLockout()->getUser($login));

        /** @var DateInterval $remaining */
        $remaining = $this->getLockoutRemainingTime($login);

        if (!$remaining->invert) {
            $failures = $this->getLockout()->getFailures($login);
        } else {
            $this->unlockUser($user);
            $failures = 0;
        }

        $failures++;

        if ($failures >= intval($this->getOption(self::OPTION_LOCKOUT_FAILED_ATTEMPTS))) {
            $this->lockUser($user);
        }

        $this->getLockout()->setFailures($login, $failures);
    }

    /**
     * @param $user
     * @return bool
     */
    public function lockUser(User $user)
    {
        $currentUser = UserHelper::getUser(tao_models_classes_UserService::singleton()->getCurrentUser());

        if (!$currentUser) {
            $currentUser = $user;
        }

        if (!$this->isLockable($user)) {
            return false;
        }

        $this->getLockout()->setLockedStatus(UserHelper::getUserLogin($user), $currentUser->getIdentifier());

        return true;
    }

    /**
     * @param $user
     * @return bool
     */
    public function unlockUser(User $user)
    {
        $login = UserHelper::getUserLogin($user);

        $this->getLockout()->setUnlockedStatus($login);
        $this->getLockout()->setFailures($login, 0);

        return true;
    }

    /**
     * @param $login
     * @return bool
     * @throws \core_kernel_users_Exception
     * @throws \Exception
     */
    public function isLocked($login)
    {
        $status = $this->getLockout()->getStatus($login);

        if (empty($status)) {
            return false;
        }

        // hard lockout, only admin can reset
        if ($status && $this->getOption(self::OPTION_USE_HARD_LOCKOUT)) {
            return true;
        }

        $lockedBy = UserHelper::getUser($this->getLockout()->getLockedBy($login));
        $user = UserHelper::getUser($this->getLockout()->getUser($login));

        if (empty($lockedBy)) {
            return false;
        }

        if ($lockedBy->getIdentifier() !== $user->getIdentifier()) {
            return true;
        }

        $lockoutPeriod = new DateInterval($this->getOption(self::OPTION_SOFT_LOCKOUT_PERIOD));
        $lastFailureTime = (new DateTimeImmutable)->setTimestamp(intval((string)$this->getLockout()->getLastFailureTime($login)));

        return $lastFailureTime->add($lockoutPeriod) > new DateTimeImmutable;
    }

    /**
     * @param $user
     * @return bool
     */
    public function isLockable(User $user)
    {
        if ($user->getIdentifier() === LOCAL_NAMESPACE . TaoOntology::DEFAULT_USER_URI_SUFFIX) {
            return false;
        }

        $nonLockingRoles = $this->getOption(self::OPTION_NON_LOCKING_ROLES);

        if ($nonLockingRoles && is_array($nonLockingRoles) && count($nonLockingRoles)) {
            return (bool) !count(array_intersect($user->getRoles(), $nonLockingRoles));
        }

        return true;
    }

    /**
     * @param $login
     * @return bool|DateInterval
     * @throws \Exception
     */
    public function getLockoutRemainingTime($login)
    {
        $lastFailure = $this->getLockout()->getLastFailureTime($login);

        $unlockTime = (new DateTime('now'))
            ->setTimestamp($lastFailure->literal)
            ->add(new DateInterval($this->getOption(self::OPTION_SOFT_LOCKOUT_PERIOD)));

        return (new DateTime('now'))->diff($unlockTime);
    }

    /**
     * @param $login
     * @return bool|int|mixed
     * @throws \core_kernel_users_Exception
     */
    public function getLockoutRemainingAttempts($login)
    {
        $user = UserHelper::getUser($this->getLockout()->getUser($login));

        if (!$this->isLockable($user)) {
            return false;
        }

        $allowedAttempts = $this->getOption(self::OPTION_LOCKOUT_FAILED_ATTEMPTS);
        $failedAttempts = $this->getLockout()->getFailures($login);

        $rest = $allowedAttempts - $failedAttempts;

        if ($rest < 0) {
            return false;
        }

        return $rest;
    }

    /**
     * @param $login
     * @return array
     * @throws \Exception
     */
    public function getStatusDetails($login)
    {
        $user = UserHelper::getUser($this->getLockout()->getUser($login));

        $isLocked = $this->isLocked($login);

        if (!$isLocked) {
            $this->unlockUser($user);

            return [
                'locked' => false,
                'auto' => false,
                'status' => __('enabled'),
                'remaining' => null,
                'lockable' => $this->isLockable($user)
            ];
        }

        $remaining = $this->getLockoutRemainingTime($login);
        $lockedBy = UserHelper::getUser($this->getLockout()->getLockedBy($login));

        $autoLocked = false;

        if ($lockedBy->getIdentifier() !== $user->getIdentifier()) {
            $status = __('locked by %s', UserHelper::getUserLogin($lockedBy));
        } else {
            $autoLocked = true;
            $status = $this->getOption(self::OPTION_USE_HARD_LOCKOUT)
                ? __('self-locked')
                : __('auto unlocked in %s', tao_helpers_Date::displayInterval($remaining));
        }

        return [
            'locked' => $isLocked,
            'auto' => $autoLocked,
            'status' => $status,
            'remaining' => $remaining,
            'lockable' => $this->isLockable($user)
        ];
    }
}
