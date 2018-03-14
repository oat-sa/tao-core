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

use common_Logger;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use oat\oatbox\service\ConfigurableService;
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

    public function setLockoutImplementation($implementation = RdfLockoutStorage::class)
    {
        $this->setOption(self::OPTION_LOCKOUT_STORAGE, $implementation);
    }

    /**
     * Returns proper lockout implementation
     * @return LockoutStorage
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
     */
    public function catchFailedLogin(LoginFailedEvent $event)
    {
        $this->increaseLoginFails($event->getLogin());
    }

    /**
     * @param LoginSucceedEvent $event
     */
    public function catchSucceedLogin(LoginSucceedEvent $event)
    {
        $this->unlockUser($event->getLogin());
    }

    /**
     * @param string $login
     */
    private function increaseLoginFails($login)
    {
        $failures = $this->getLockout()->getFailures($login);

        $failures++;

        if ($failures >= intval($this->getOption(self::OPTION_LOCKOUT_FAILED_ATTEMPTS))) {
            $this->lockUser($this->getLockout()->getUser($login));
        }

        $this->getLockout()->setFailures($login, $failures);
    }

    /**
     * @param $user
     * @return bool
     */
    public function lockUser($user)
    {
        $currentUser = tao_models_classes_UserService::singleton()->getCurrentUser();

        if (!$currentUser) {
            $currentUser = $user;
        }

        $user = UserHelper::getUser($user);

        if ($user->getIdentifier() === LOCAL_NAMESPACE . TaoOntology::DEFAULT_USER_URI_SUFFIX) {
            common_Logger::i('Default user can not be locked');
            return false;
        }

        $this->getLockout()->setLockedStatus(UserHelper::getUserLogin($user), $currentUser);

        return true;
    }

    /**
     * @param $user
     * @return bool
     */
    public function unlockUser($user)
    {
        $login = UserHelper::getUserLogin(UserHelper::getUser($user));

        $this->getLockout()->setUnlockedStatus($login);
        $this->getLockout()->setFailures($login, 0);

        return true;
    }

    /**
     * @param $login
     * @return bool
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
     */
    public function getLockoutRemainingAttempts($login)
    {
        $user = UserHelper::getUser($this->getLockout()->getUser($login));

        if ($user->getIdentifier() === LOCAL_NAMESPACE . TaoOntology::DEFAULT_USER_URI_SUFFIX) {
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
     *   boolean        array.locked - returns true if user is locked else false
     *   boolean        array.auto - returns true if user auto locked (locked by himself) else false
     *   string         array.status - human readable string with actual account status
     *   DateInterval   array.remaining - returns valid period of time that left before user will be unlocked, may be null if not applicable
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
                'remaining' => null
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
            'remaining' => $remaining
        ];
    }
}
