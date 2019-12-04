<?php

declare(strict_types=1);

return new oat\tao\model\clientConfig\ClientConfigService([
    'configs' => [
        'themesAvailable' => new oat\tao\model\clientConfig\sources\ThemeConfig(),
    ],
]);
