<?php
/**
 * Default config header created during install
 */

use oat\tao\model\mutex\LockService;

return new LockService([
    LockService::OPTION_PERSISTENCE => 'default'
]);
