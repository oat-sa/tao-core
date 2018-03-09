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
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\event\LoginFailedEvent;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\TaoOntology;
use tao_helpers_Date;

/**
 * Class UserLocksService
 * @package oat\tao\model\user
 */
class UserLocksService extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'tao/userlocks';

    /** Use hard lock for failed logon. Be default soft lock will be used */
    const OPTION_USE_HARD_LOCKOUT = 'use_hard_lockout';

    /** Amount of failed login attempts before lockout */
    const OPTION_LOCKOUT_FAILED_ATTEMPTS = 'lockout_failed_attempts';

    /** Duration of soft lock out */
    const OPTION_SOFT_LOCKOUT_PERIOD = 'soft_lockout_period';

    /**
     * @param LoginFailedEvent $event
     * @throws \core_kernel_persistence_Exception
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
        $this->resetLoginFails($event->getLogin());
    }

    /**
     * Resets count of login fails in case successful login
     * @param $login
     */
    private function resetLoginFails($login)
    {
        $user = core_kernel_users_Service::singleton()->getOneUser($login);

        $user->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOGON_FAILURES), 0);
        $user->removePropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_ACCOUNT_STATUS));
        $user->removePropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY));
    }

    /**
     * @param string $login
     * @throws \core_kernel_persistence_Exception
     */
    private function increaseLoginFails($login)
    {
        $user = core_kernel_users_Service::singleton()->getOneUser($login);

        $failedLoginCountProperty = $this->getProperty(TaoOntology::PROPERTY_USER_LOGON_FAILURES);
        $failedLoginCount = (intval((string)$user->getOnePropertyValue($failedLoginCountProperty))) + 1;

        if ($failedLoginCount >= intval($this->getOption(self::OPTION_LOCKOUT_FAILED_ATTEMPTS))) {
            $this->lockUser($user);
        }

        $user->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LAST_LOGON_FAILURE_TIME), time());
        $user->editPropertyValues($failedLoginCountProperty, $failedLoginCount);
    }

    /**
     * @param core_kernel_classes_Resource $user
     * @param core_kernel_classes_Resource $by
     * @return bool
     */
    public function lockUser(core_kernel_classes_Resource $user, core_kernel_classes_Resource $by = null)
    {
        $user->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_ACCOUNT_STATUS), TaoOntology::PROPERTY_USER_STATUS_LOCKED);
        $user->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY), $by ?: $user);

        return true;
    }

    /**
     * @param core_kernel_classes_Resource $user
     * @return bool
     */
    public function unlockUser(core_kernel_classes_Resource $user)
    {
        $user->removePropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_ACCOUNT_STATUS));
        $user->removePropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY));

        return true;
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
            $lockoutPeriod = new \DateInterval($this->getOption(self::OPTION_SOFT_LOCKOUT_PERIOD));

            /** @var core_kernel_classes_Literal $lastFailureTimePropertyValue */
            $lastFailureTimePropertyValue = $user->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_LAST_LOGON_FAILURE_TIME));

            $lastFailureTime = new \DateTimeImmutable;
            $lastFailureTime = $lastFailureTime->setTimestamp($lastFailureTimePropertyValue->literal);

            return $lastFailureTime->add($lockoutPeriod) > new \DateTimeImmutable();
        }
    }

    /**
     * @param $login
     * @return array
     * @throws \Exception
     * @throws \core_kernel_persistence_Exception
     */
    public function getStatusDetails($login)
    {
        $user = core_kernel_users_Service::singleton()->getOneUser($login);

        $isLocked = $this->isLocked($login);

        if (!$isLocked) {
            $this->unlockUser($user);

            return ['locked' => false, 'status' => __('enabled')];
        }

        $userProperties = $user->getPropertiesValues([
            TaoOntology::PROPERTY_USER_LOCKED_BY,
            TaoOntology::PROPERTY_USER_LAST_LOGON_FAILURE_TIME
        ]);

        $lockedBy = empty($userProperties[TaoOntology::PROPERTY_USER_LOCKED_BY]) ? null : current($userProperties[TaoOntology::PROPERTY_USER_LOCKED_BY]);
        $lastFailure = empty($userProperties[TaoOntology::PROPERTY_USER_LAST_LOGON_FAILURE_TIME]) ? null : current($userProperties[TaoOntology::PROPERTY_USER_LAST_LOGON_FAILURE_TIME]);

        if ($lockedBy->getUri() === $user->getUri()) {
            if ($this->getOption(self::OPTION_USE_HARD_LOCKOUT)) {
                $status = __('self-locked');
            } else {
                $unlockTime = (new DateTime('now'))
                    ->setTimestamp($lastFailure->literal)
                    ->add(new DateInterval($this->getOption(self::OPTION_SOFT_LOCKOUT_PERIOD)));

                $status = __('auto unlocked in %s',
                    tao_helpers_Date::displayInterval((new DateTime('now'))->diff($unlockTime))
                );
            }
        } else {
            $blockedByUsername = $lockedBy->getOnePropertyValue($this->getProperty(GenerisRdf::PROPERTY_USER_LOGIN));
            $status = __('locked by %s', $blockedByUsername);
        }

        return ['locked' => $isLocked, 'status' => $status];
    }
}
