<?php

return new \oat\tao\model\cliArgument\ArgumentService(array(
    'arguments' => array(
        new \oat\tao\model\cliArgument\argument\implementation\Group(array(
            new \oat\tao\model\cliArgument\argument\implementation\verbose\Debug(),
            new \oat\tao\model\cliArgument\argument\implementation\verbose\Info(),
            new \oat\tao\model\cliArgument\argument\implementation\verbose\Notice(),
            new \oat\tao\model\cliArgument\argument\implementation\verbose\Error(),
        ))
    )
));