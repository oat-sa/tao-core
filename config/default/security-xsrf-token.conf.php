<?php
/**
 * Default config header
 *
 * To replace this add a file /home/bertrand/dev/projects/package-tao-dev/tao/config/header/security-xsrf-token.conf.php
 */

return new oat\tao\model\security\xsrf\TokenService(array(
    'store' => unserialize('O:45:"oat\\tao\\model\\security\\xsrf\\TokenStoreSession":0:{}'),
    'poolSize' => 10,
    'timeLimit' => 0
));
