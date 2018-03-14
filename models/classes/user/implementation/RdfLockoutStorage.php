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
use core_kernel_users_Service;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\LockoutStorage;

/**
 * Class RdfLockoutStorage
 * @package oat\tao\model\user\implementation
 */
class RdfLockoutStorage implements LockoutStorage
{
    use OntologyAwareTrait;

    /**
     * @param string $login
     * @return core_kernel_classes_Resource
     */
    public function getUser($login)
    {
        return core_kernel_users_Service::singleton()->getOneUser($login);
    }

    /**
     * @param $login
     * @return \core_kernel_classes_Container
     * @throws \core_kernel_persistence_Exception
     */
    public function getStatus($login)
    {
        return $this->getUser($login)->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_ACCOUNT_STATUS));
    }

    /**
     * @param $login
     * @param $by
     */
    public function setLockedStatus($login, $by)
    {
        $this->getUser($login)->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_ACCOUNT_STATUS), TaoOntology::PROPERTY_USER_STATUS_LOCKED);
        $this->getUser($login)->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY), $by);

    }

    /**
     * @param $login
     */
    public function setUnlockedStatus($login)
    {
        $this->getUser($login)->removePropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_ACCOUNT_STATUS));
        $this->getUser($login)->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY), null);
    }

    /**
     * @param string $login
     * @return int
     * @throws \core_kernel_persistence_Exception
     */
    public function getFailures($login)
    {
        return (intval((string)$this->getUser($login)->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_LOGON_FAILURES))));
    }

    /**
     * @param string $login
     * @param $value
     * @return bool
     */
    public function setFailures($login, $value)
    {
        $user = $this->getUser($login);

        $user->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOGON_FAILURES), $value);

        if ($value) {
            $user->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LAST_LOGON_FAILURE_TIME), time());
        }

        return true;
    }

    /**
     * @param string $login
     * @return mixed
     * @throws \core_kernel_persistence_Exception
     */
    public function getLastFailureTime($login)
    {
        return $this->getUser($login)->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_LAST_LOGON_FAILURE_TIME));
    }

    /**
     * @param $login
     * @return \core_kernel_classes_Container
     * @throws \core_kernel_persistence_Exception
     */
    public function getLockedBy($login)
    {
        return $this->getUser($login)->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY));
    }
}
