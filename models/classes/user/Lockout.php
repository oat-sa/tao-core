<?php

namespace oat\tao\model\user;

interface Lockout
{
    public function getLogonFailures($login);

    public function getLastLogonFailureTime($login);

//    public function getLockedBy($login);

    public function getUser($login);

    public function setLogonFailures($user, $value);

    public function lockUser($user, $by = null);

    public function unlockUser($login);

    public function isAutoLocked($login);
}
