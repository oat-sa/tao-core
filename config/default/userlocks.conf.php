<?php

return new oat\tao\model\user\UserLocksService([

    /** Implementation of user lockouts */
    oat\tao\model\user\UserLocks::OPTION_USER_LOCK_IMPLEMENTATION => \oat\tao\model\user\implementation\NoLockout::class,
    
    /** Use hard lock for failed logon. By default soft lock will be used */
    oat\tao\model\user\UserLocks::OPTION_USE_HARD_LOCKOUT => false,

    /** Amount of failed login attempts before locking */
    oat\tao\model\user\UserLocks::OPTION_LOCKOUT_FAILED_ATTEMPTS => 6,

    /** Duration of soft lock */
    oat\tao\model\user\UserLocks::OPTION_SOFT_LOCKOUT_PERIOD => 'PT30M'
]);
