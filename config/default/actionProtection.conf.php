<?php

use oat\tao\model\security\ActionProtector;
use oat\tao\model\security\DataAccess\Repository\SecuritySettingsRepository;
use oat\tao\model\service\SettingsStorage;

return new ActionProtector(
    new SecuritySettingsRepository(
        new SettingsStorage(
            [
                'persistence'   => 'default_kv',
                'key_namespace' => 'tao:settings:',
            ]
        )
    ),
    [
        'X-Content-Type-Options: nosniff',
    ]
);
