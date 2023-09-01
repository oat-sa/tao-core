<?php

return new \oat\tao\model\cliArgument\ArgumentService([
    'arguments' => [
        new \oat\tao\model\cliArgument\argument\implementation\Group([
            new \oat\tao\model\cliArgument\argument\implementation\verbose\Debug(),
            new \oat\tao\model\cliArgument\argument\implementation\verbose\Info(),
            new \oat\tao\model\cliArgument\argument\implementation\verbose\Notice(),
            new \oat\tao\model\cliArgument\argument\implementation\verbose\Error(),
        ])
    ]
]);
