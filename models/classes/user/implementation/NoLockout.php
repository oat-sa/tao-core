<?php

namespace oat\tao\model\user\implementation;

use core_kernel_users_Service;
use oat\tao\model\user\Lockout;

class NoLockout implements Lockout
{
    public function getUser($login) {
        return core_kernel_users_Service::singleton()->getOneUser($login);
    }

    public function getStatus($login) {}

    public function setLockedStatus($login) {}

    public function setUnlockedStatus($login) {}

    public function getFailures($login) {}

    public function setFailures($login, $value) {}

    public function getLastFailureTime($login) {}

    public function getLockedBy($login) {}

    public function setLockedBy($login, $by) {}
}
