<?php
return new oat\tao\model\security\xsrf\TokenService(array(
    'store' => new oat\tao\model\security\xsrf\TokenStoreSession(),
    'poolSize' => 10,
    'timeLimit' => 0
));
