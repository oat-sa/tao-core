<?php

use oat\tao\model\service\SettingsStorage;

return new SettingsStorage([
    SettingsStorage::OPTION_PERSISTENCE => 'default_kv',
    SettingsStorage::OPTION_KEY_NAMESPACE => 'tao:settings:'
]);
