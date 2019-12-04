<?php

declare(strict_types=1);

/**
 * Default config header
 *
 * To replace this add a file tao/conf/header/entrypoint.conf.php
 */

return new oat\tao\model\entryPoint\EntryPointService([
    'existing' => [
    ],
    'postlogin' => [
    ],
    'prelogin' => [
    ],
]);
