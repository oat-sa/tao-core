<?php

declare(strict_types=1);

return new oat\tao\model\security\ActionProtector([
    'frameSourceWhitelist' => [
        "'self'",
    ],
]);
