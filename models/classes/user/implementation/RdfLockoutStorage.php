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

use core_kernel_classes_Resource;
use core_kernel_users_Exception;
use core_kernel_users_Service;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\user\LockoutStorage;

/**
 * Class RdfLockoutStorage
 * @package oat\tao\model\user\implementation
 */
class RdfLockoutStorage implements LockoutStorage
{
    use OntologyAwareTrait;

    const PROPERTY_USER_ACCOUNT_STATUS = 'http://www.tao.lu/Ontologies/TAO.rdf#accountStatus';

    const PROPERTY_USER_STATUS_LOCKED = 'http://www.tao.lu/Ontologies/TAO.rdf#Locked';

    const PROPERTY_USER_LOCKED_BY = 'http://www.tao.lu/Ontologies/TAO.rdf#lockedBy';

    const PROPERTY_USER_LOGON_FAILURES = 'http://www.tao.lu/Ontologies/TAO.rdf#logonFailures';

    const PROPERTY_USER_LAST_LOGON_FAILURE_TIME = 'http://www.tao.lu/Ontologies/TAO.rdf#lastLogonFailureTime';

    /**
     * @param string $login
     * @return core_kernel_classes_Resource
     * @throws core_kernel_users_Exception
     */
    public function getUser($login)
    {
        $user = core_kernel_users_Service::singleton()->getOneUser($login);

        if (is_null($user)) {
            throw new core_kernel_users_Exception(sprintf('Requested user with login %s not found.', $login));
        }

        return $user;
    }

    /**
     * @param $login
     * @return \core_kernel_classes_Container
     * @throws \core_kernel_persistence_Exception
     * @throws core_kernel_users_Exception
     */
    public function getStatus($login)
    {
        return $this->getUser($login)->getOnePropertyValue($this->getProperty(self::PROPERTY_USER_ACCOUNT_STATUS));
    }

    /**
     * @param $login
     * @param $by
     * @throws core_kernel_users_Exception
     */
    public function setLockedStatus($login, $by)
    {
        $this->getUser($login)->editPropertyValues($this->getProperty(self::PROPERTY_USER_ACCOUNT_STATUS), self::PROPERTY_USER_STATUS_LOCKED);
        $this->getUser($login)->editPropertyValues($this->getProperty(self::PROPERTY_USER_LOCKED_BY), $by);

    }

    /**
     * @param $login
     * @throws core_kernel_users_Exception
     */
    public function setUnlockedStatus($login)
    {
        $this->getUser($login)->removePropertyValues($this->getProperty(self::PROPERTY_USER_ACCOUNT_STATUS));
        $this->getUser($login)->editPropertyValues($this->getProperty(self::PROPERTY_USER_LOCKED_BY), null);
    }

    /**
     * @param string $login
     * @return int
     * @throws \core_kernel_persistence_Exception
     * @throws core_kernel_users_Exception
     */
    public function getFailures($login)
    {
        return (intval((string)$this->getUser($login)->getOnePropertyValue($this->getProperty(self::PROPERTY_USER_LOGON_FAILURES))));
    }

    /**
     * @param string $login
     * @param $value
     * @return bool
     * @throws core_kernel_users_Exception
     */
    public function setFailures($login, $value)
    {
        $user = $this->getUser($login);

        $user->editPropertyValues($this->getProperty(self::PROPERTY_USER_LOGON_FAILURES), $value);

        if ($value) {
            $user->editPropertyValues($this->getProperty(self::PROPERTY_USER_LAST_LOGON_FAILURE_TIME), time());
        }

        return true;
    }

    /**
     * @param string $login
     * @return mixed
     * @throws \core_kernel_persistence_Exception
     * @throws core_kernel_users_Exception
     */
    public function getLastFailureTime($login)
    {
        return $this->getUser($login)->getOnePropertyValue($this->getProperty(self::PROPERTY_USER_LAST_LOGON_FAILURE_TIME));
    }

    /**
     * @param $login
     * @return \core_kernel_classes_Container
     * @throws \core_kernel_persistence_Exception
     * @throws core_kernel_users_Exception
     */
    public function getLockedBy($login)
    {
        return $this->getUser($login)->getOnePropertyValue($this->getProperty(self::PROPERTY_USER_LOCKED_BY));
    }
}
