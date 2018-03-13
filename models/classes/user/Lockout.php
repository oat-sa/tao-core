<?php

namespace oat\tao\model\user;

interface Lockout
{
    public function getUser($login);

    public function getStatus($login);

    public function setLockedStatus($login);

    public function setUnlockedStatus($login);

    public function getFailures($login);

    public function setFailures($login, $value);

    public function getLastFailureTime($login);

    public function getLockedBy($login);

    public function setLockedBy($login, $by);
}
