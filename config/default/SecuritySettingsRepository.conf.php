<?php declare(strict_types=1);

use oat\tao\model\security\DataAccess\Repository\SecuritySettingsRepository;
use oat\tao\model\service\SettingsStorage;

return new SecuritySettingsRepository(
    new SettingsStorage(
        [
            'persistence'   => 'default_kv',
            'key_namespace' => 'tao:settings:',
        ]
    )
);
