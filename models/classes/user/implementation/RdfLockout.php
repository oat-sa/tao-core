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

    private $user;

    /**
     * @param string $login
     * @return core_kernel_classes_Resource
     */
    public function getUser($login)
    {
        if (!$this->user) {
            $this->user = core_kernel_users_Service::singleton()->getOneUser($login);
        }

        return $this->user;
    }

    /**
     * @param string $login
     * @return int
     * @throws \core_kernel_persistence_Exception
     */
    public function getLogonFailures($login)
    {
        return (intval((string)$this->getUser($login)->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_LOGON_FAILURES))));
    }

    /**
     * @param string $login
     * @param $value
     * @return bool
     */
    public function setLogonFailures($login, $value)
    {
        $user = $this->getUser($login);

        $user->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOGON_FAILURES), $value);
        $user->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LAST_LOGON_FAILURE_TIME), time());

        return true;
    }

    /**
     * @param string $login
     * @return mixed
     * @throws \core_kernel_persistence_Exception
     */
    public function getLastLogonFailureTime($login)
    {
        return $this->getUser($login)->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_LAST_LOGON_FAILURE_TIME));
    }

//    /**
//     * @param $login
//     * @return User
//     * @throws \core_kernel_persistence_Exception
//     */
//    public function getLockedBy($login)
//    {
//        return $this->getUser($login)->getOnePropertyValue($this->getProperty(GenerisRdf::PROPERTY_USER_LOGIN));
////        return new core_kernel_users_GenerisUser($this->getUser($login)->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY)));
//    }

    /**
     * @param $login
     * @param $by
     * @return bool
     */
    public function lockUser($login, $by = null)
    {
        if ($by && is_string($by)) {
            $by = core_kernel_users_Service::singleton()->getOneUser($by);
        }

        $this->getUser($login)->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_ACCOUNT_STATUS), TaoOntology::PROPERTY_USER_STATUS_LOCKED);
        $this->getUser($login)->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY), $by ?: $this->getUser($login));

        return true;
    }

    /**
     * @param $login
     * @return bool
     */
    public function unlockUser($login)
    {
        $this->getUser($login)->editPropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOGON_FAILURES), 0);
        $this->getUser($login)->removePropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_ACCOUNT_STATUS));
        $this->getUser($login)->removePropertyValues($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY));

        return true;
    }

    /**
     * @param $login
     * @return bool
     * @throws \core_kernel_persistence_Exception
     */
    public function isAutoLocked($login)
    {
        $user = $this->getUser($login);

        /** @var core_kernel_classes_Resource $lockedBy */
        $lockedBy = $user->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_USER_LOCKED_BY));

        if (!$lockedBy) {
            return false;
        }

        return $user->getUri() === $lockedBy->getUri();
    }
}
