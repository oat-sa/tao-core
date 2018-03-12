<?php

return new oat\tao\model\user\UserLocksService([
    
    /** Use hard lock for failed logon. By default soft lock will be used */
    'use_hard_lockout' => false,

    /** Amount of failed login attempts before locking */
    'lockout_failed_attempts' => 6,

    /** Duration of soft lock */
    'soft_lockout_period' => 'PT30M'
]);
