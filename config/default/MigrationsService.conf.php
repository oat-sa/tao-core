<?php
/**
 * Default config header created during install
 */

use oat\tao\model\migrations\MigrationsService;

return new MigrationsService([
    MigrationsService::OPTION_PERSISTENCE_ID => 'default',
]);
