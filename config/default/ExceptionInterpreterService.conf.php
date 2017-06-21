<?php
/**
 * Default config header
 *
 * To replace this add a file D:\domains\package-tao\tao\config/header/ExceptionInterpreterService.conf.php
 */
use oat\tao\model\mvc\error\ExceptionInterpreterService;

return new ExceptionInterpreterService([
    ExceptionInterpreterService::OPTION_INTERPRETERS => [
        'Exception' => 'oat\\tao\\model\\mvc\\error\\ExceptionInterpretor'
    ]
]);
