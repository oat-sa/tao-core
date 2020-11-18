<?php
return new oat\tao\model\security\xsrf\TokenService([
    'store' => new oat\tao\model\security\xsrf\TokenStoreSession(),
    'poolSize' => 10,
    'timeLimit' => 0,
    'validateTokens' => true
]);
