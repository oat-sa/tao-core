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

use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;

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
     * Resets count of login fails in case successful login
     * @param $user
     */
    private function resetLoginFails($login)
    {
        $this->getUser($login)->editPropertyValues($this->getProperty(GenerisRdf::PROPERTY_USER_LOGON_FAILURES), 0);
        $this->getUser($login)->removePropertyValues($this->getProperty(GenerisRdf::PROPERTY_USER_STATUS));
        $this->getUser($login)->removePropertyValues($this->getProperty(GenerisRdf::PROPERTY_USER_BLOCKED_BY));
    }

    /**
     * @param string $login
     * @throws \core_kernel_persistence_Exception
     */
    private function increaseLoginFails($login)
    {
        $user = core_kernel_users_Service::singleton()->getOneUser($login);

        $failedLoginCountProperty = $this->getProperty(GenerisRdf::PROPERTY_USER_LOGON_FAILURES);
        $failedLoginCount = (intval((string)$user->getOnePropertyValue($failedLoginCountProperty))) + 1;

        if ($failedLoginCount >= intval($this->getOption(self::OPTION_LOCKOUT_FAILED_ATTEMPTS))) {
            $this->blockUser($user);
        }

        $user->editPropertyValues($this->getProperty(GenerisRdf::PROPERTY_USER_LAST_LOGON_FAILURE_TIME), time());
        $user->editPropertyValues($failedLoginCountProperty, $failedLoginCount);
    }

    /**
     * // should be public for using in controller
     * @param core_kernel_classes_Resource $user
     * @param core_kernel_classes_Resource $by
     * @return bool
     */
    public function lockUser($user, $by = null)
    {
        $user->editPropertyValues($this->getProperty(GenerisRdf::PROPERTY_USER_STATUS), GenerisRdf::PROPERTY_USER_STATUS_BLOCKED);
        $user->editPropertyValues($this->getProperty(GenerisRdf::PROPERTY_USER_BLOCKED_BY), $by ?: $user);

        return true;
    }

    /**
     * @param core_kernel_classes_Resource $user
     * @return bool
     */
    public function unlockUser(core_kernel_classes_Resource $user)
    {
        $user->removePropertyValues($this->getProperty(GenerisRdf::PROPERTY_USER_STATUS));
        $user->removePropertyValues($this->getProperty(GenerisRdf::PROPERTY_USER_BLOCKED_BY));

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

        if (empty((string)$user->getOnePropertyValue($this->getProperty(GenerisRdf::PROPERTY_USER_STATUS)))) {
            return false;
        }

        // hard lockout, only admin can reset
        if ($this->getOption(self::OPTION_USE_HARD_LOCKOUT)) {
            return true;
        } else {
            $lockoutPeriod = new DateInterval($this->getOption(self::OPTION_SOFT_LOCKOUT_PERIOD));

            /** @var core_kernel_classes_Literal $lastFailureTimePropertyValue */
            $lastFailureTimePropertyValue = $user->getOnePropertyValue($this->getProperty(GenerisRdf::PROPERTY_USER_LAST_LOGON_FAILURE_TIME));

            $lastFailureTime = new DateTimeImmutable;
            $lastFailureTime = $lastFailureTime->setTimestamp($lastFailureTimePropertyValue->literal);

            return $lastFailureTime->add($lockoutPeriod) > new DateTimeImmutable();
        }
    }

    public function getActualStatus($login)
    {
        if (!$this->isBlocked($login)) {
            $this->resetUser($login);
        }

        // process status and return status info
        //        проверить актуальный статус, если не актуально - обновить состояние и вернуть данные


    }
}
