<?php

declare(strict_types=1);

return new oat\tao\model\security\xsrf\TokenService([
    'store' => new oat\tao\model\security\xsrf\TokenStoreSession(),
    'poolSize' => 10,
    'timeLimit' => 0,
]);
