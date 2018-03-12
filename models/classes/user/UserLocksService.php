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

use core_kernel_classes_Literal;
use core_kernel_classes_Resource;
use core_kernel_users_Service;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\event\LoginFailedEvent;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\implementation\RdfLockout;
use tao_helpers_Date;

/**
 * Class UserLocksService
 * @package oat\tao\model\user
 */
class UserLocksService extends ConfigurableService implements UserLocks
{
    use OntologyAwareTrait;

    /** @var Lockout */
    private $lockout;

    /**
     * @return RdfLockout|Lockout
     */
    protected function getLockout()
    {
        if (!$this->lockout || !$this->lockout instanceof Lockout) {
            $this->lockout = new RdfLockout(); // todo: set proper implementation of lockout based on configuration
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
        $failures = $this->getLockout()->getLogonFailures($login);

        if (($failures++) >= intval($this->getOption(self::OPTION_LOCKOUT_FAILED_ATTEMPTS))) {
            $this->lockUser($login);
        }

        $this->getLockout()->setLogonFailures($login, $failures);
    }

    /**
     * @param $login
     * @param $by
     * @return bool
     */
    public function lockUser($login, $by = null)
    {
        return $this->getLockout()->lockUser($login, $by);
    }

    /**
     * @param $login
     * @return bool
     */
    public function unlockUser($login)
    {
        return $this->getLockout()->unlockUser($login);
    }

    /**
     * @param $login
     * @return bool
     * @throws \core_kernel_persistence_Exception
     * @throws \Exception
     */
    public function isLocked($login)
    {
        $user = core_kernel_users_Service::singleton()->getOneUser($login);

        if (empty((string)$user->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_ACCOUNT_STATUS)))) {
            return false;
        }

        // hard lockout, only admin can reset
        if ($this->getOption(self::OPTION_USE_HARD_LOCKOUT)) {
            return true;
        } else {

            /** @var core_kernel_classes_Resource $lockedBy */
            $lockedBy = $user->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY));

            if (empty($lockedBy)) {
                return false;
            }

            if ($lockedBy->getUri() !== $user->getUri()) {
                return true;
            }

            $lockoutPeriod = new DateInterval($this->getOption(self::OPTION_SOFT_LOCKOUT_PERIOD));

            /** @var core_kernel_classes_Literal $lastFailureTimePropertyValue */
            $lastFailureTimePropertyValue = $user->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_LAST_LOGON_FAILURE_TIME));

            $lastFailureTime = new \DateTimeImmutable;
            $lastFailureTime = $lastFailureTime->setTimestamp($lastFailureTimePropertyValue->literal);

            return $lastFailureTime->add($lockoutPeriod) > new DateTimeImmutable;
        }
    }

    /**
     * @param $login
     * @return bool|DateInterval
     * @throws \Exception
     */
    public function getLockoutRemainingTime($login)
    {
        $lastFailure = $this->getLockout()->getLastLogonFailureTime($login);

        $unlockTime = (new DateTime('now'))
            ->setTimestamp($lastFailure->literal)
            ->add(new DateInterval($this->getOption(self::OPTION_SOFT_LOCKOUT_PERIOD)));

        return (new DateTime('now'))->diff($unlockTime);
    }

    /**
     * @param $login
     * @return array
     * @throws \Exception
     * @throws \core_kernel_persistence_Exception
     */
    public function getStatusDetails($login)
    {
        $isLocked = $this->isLocked($login);

        if (!$isLocked) {
            $this->unlockUser($login);

            return [
                'locked' => false,
                'auto' => false,
                'status' => __('enabled'),
                'remaining' => null
            ];
        }

        $remaining = $this->getLockoutRemainingTime($login);
        $autoLocked = false;

        if ($this->getLockout()->isAutoLocked($login)) {
            $autoLocked = true;
            $status = $this->getOption(self::OPTION_USE_HARD_LOCKOUT)
                ? __('self-locked')
                : __('auto unlocked in %s', tao_helpers_Date::displayInterval($remaining));
        } else {
            
//            $blockedByUsername = $lockedBy->getPropertyValues($this->getProperty(GenerisRdf::PROPERTY_USER_LOGIN));
//            $blockedByUsername = $lockedBy->getOnePropertyValue($this->getProperty(GenerisRdf::PROPERTY_USER_LOGIN));
//            $status = __('locked by %s', $blockedByUsername);
            $status = __('locked by %s', 'undef');
        }

        return [
            'locked' => $isLocked,
            'auto' => $autoLocked,
            'status' => $status,
            'remaining' => $remaining
        ];
    }
}
