<?php

return new \oat\tao\model\mvc\psr7\ActionExecutor(
        [
            'executor' => 
            [
                 \oat\tao\model\mvc\psr7\executor\Psr7Executor::class,
            ]
        ]
    );
