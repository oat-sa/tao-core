<?php
/**
 * Default config header created during install
 */

return new oat\tao\model\entryPoint\EntryPointService(array(
    'existing' => array(
        'passwordreset' => new oat\tao\model\entryPoint\PasswordReset(),
        'dummy' => new oat\tao\test\unit\service\DummyEntryPoint(),
    ),
    'postlogin' => array(
        'passwordreset',
        'dummy',
    ),
    'prelogin' => array(
        'passwordreset',
        'dummy',
    ),
    'new_tag' => [
        'passwordreset',
        'dummy',
    ]
));
