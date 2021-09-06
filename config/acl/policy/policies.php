<?php

use oat\tao\model\accessControl\ActionAccessControl;
use oat\tao\model\user\TaoRoles;

return [
    //Policy 1
    [
        'name' => 'MainAccessPolicy',
        'version' => 1,
        'actionPermissions' => [
            tao_actions_PocController::class => [
                'someActionOrFeature' => [
                    TaoRoles::ANONYMOUS => ActionAccessControl::DENY,
                    TaoRoles::BACK_OFFICE => ActionAccessControl::READ,
                ],
            ],
        ],
        'routePermissions' => [
            TaoRoles::BACK_OFFICE => [
                [
                    'ext' => 'tao',
                    'mod' => 'PocController',
                    'act' => 'index',
                ],
            ]
        ]
    ],
    //Policy N... []
];
