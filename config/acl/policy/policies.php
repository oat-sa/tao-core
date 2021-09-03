<?php

use oat\tao\model\accessControl\ActionAccessControl;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\user\TaoRoles;

return [
    //Policy 1
    [
        'name' => 'MainAccessPolicy',
        'version' => 1,
        'permissions' => [
            tao_actions_Languages::class => [
                'changeLanguage' => [
                    TaoRoles::ANONYMOUS => ActionAccessControl::DENY,
                    TaoRoles::BACK_OFFICE => ActionAccessControl::READ,
                ],
            ],
        ],
        'routing' => [
            [
                AccessRule::GRANT,
                TaoRoles::ANONYMOUS,
                [
                    'ext' => 'tao',
                    'mod' => 'Main',
                    'act' => 'entry'
                ]
            ]
        ]
    ],
    //Policy N... []
];
