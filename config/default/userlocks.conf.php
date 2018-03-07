<?php

return new oat\tao\model\user\UserLocksService([
    
    /** Use hard lock for failed logon. Be default soft lock will be used */
    'use_hard_lockout' => false,

    /** Amount of failed login attempts before lockout */
    'lockout_failed_attempts' => 5,

    /** Duration of soft lock out */
    'soft_lockout_period' => 'PT15M'
]);
