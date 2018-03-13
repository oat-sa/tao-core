<?php

namespace oat\tao\model\user\implementation;

use core_kernel_classes_Resource;
use core_kernel_users_Service;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdf;
use oat\oatbox\user\User;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\Lockout;

class RdfLockout implements Lockout
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
     */
    public function setLockedStatus($login)
    {
        $this->getUser($login)->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_ACCOUNT_STATUS), TaoOntology::PROPERTY_USER_STATUS_LOCKED);
    }

    /**
     * @param $login
     */
    public function setUnlockedStatus($login)
    {
        $this->getUser($login)->removePropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_ACCOUNT_STATUS));
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

    /**
     * @param $login
     * @param $by
     */
    public function setLockedBy($login, $by)
    {
        $this->getUser($login)->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY), $by);
    }
}
