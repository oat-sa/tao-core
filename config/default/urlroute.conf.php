<?php

return new \oat\tao\model\mvc\DefaultUrlService(
            [
                'default' => 
                [
                    'ext'        => 'tao',
                    'controller' => 'Main',
                    'action'     => 'index',
                ],
                'login' => 
                [
                    'ext'        => 'tao',
                    'controller' => 'Main',
                    'action'     => 'login',
                ]
            ]
        );

