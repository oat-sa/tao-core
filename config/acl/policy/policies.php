<?php

use oat\tao\model\accessControl\ActionAccessControl;
use oat\tao\model\user\TaoRoles;

return [
    //Policy 1
    [
        'id' => 'mainAccessPolicy',
        'permissions' => [
            'action' => [
                tao_actions_PocController::class => [
                    'someActionOrFeature' => [
                        TaoRoles::BACK_OFFICE => ActionAccessControl::READ,
                        TaoRoles::ANONYMOUS => ActionAccessControl::DENY,
                    ],
                ],
            ],
            'route' => [
                TaoRoles::BACK_OFFICE => [
                    [
                        'ext' => 'tao',
                        'mod' => 'PocController',
                        'act' => 'index',
                    ],
                ]
            ]
        ]
    ],
    //Policy 2
    [
        'id' => 'otherAccessPolicy',
        'permissions' => [
            'action' => [
                tao_actions_PocController::class => [
                    'someActionOrFeature' => [
                        TaoRoles::BACK_OFFICE => ActionAccessControl::DENY,
                        TaoRoles::ANONYMOUS => ActionAccessControl::GRANT,
                    ],
                ],
            ],
            'route' => [
                TaoRoles::GLOBAL_MANAGER => [
                    [
                        'ext' => 'tao',
                        'mod' => 'PocController',
                        'act' => 'index',
                    ],
                ]
            ]
        ]
    ],
    //Policy N... []
];
