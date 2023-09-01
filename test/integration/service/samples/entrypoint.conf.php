<?php

/**
 * Default config header created during install
 */

return new oat\tao\model\entryPoint\EntryPointService([
    'existing' => [
        'passwordreset' => new oat\tao\model\entryPoint\PasswordReset(),
        'deliveryServer' => new oat\taoProctoring\model\entrypoint\ProctoringDeliveryServer(),
        'guestaccess' => new oat\taoDeliveryRdf\model\guest\GuestAccess(),
        'proctoringDelivery' => new oat\taoProctoring\model\entrypoint\ProctoringDeliveryServer(),
    ],
    'postlogin' => [
        'deliveryServer',
        'backoffice',
        'proctoring',
        'childOrganization',
        'scoreReport',
        'exam',
        'testingLocationList',
        'proctoringDelivery'
    ],
    'prelogin' => [
        'guestaccess',
        'proctoringDelivery'
    ],
    'new_tag' => [
        'proctoringDelivery'
    ]
]);
