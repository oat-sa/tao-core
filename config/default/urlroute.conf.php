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
                ],
                'logout' =>
                [
                    'ext'        => 'tao',
                    'controller' => 'Main',
                    'action'     => 'logout',
                    'redirect'   =>
                        [
                            'class'   => \oat\tao\model\mvc\DefaultUrlModule\TaoActionResolver::class,
                            'options' => [
                                'action' => 'entry',
                                'controller' => 'Main',
                                'ext' => 'tao'
                            ]
                        ],
                ]
            ]
        );

