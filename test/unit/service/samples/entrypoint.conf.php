<?php

/**
 * Default config header created during install
 */

use oat\tao\model\entryPoint\Entrypoint;

return new oat\tao\model\entryPoint\EntryPointService([
    'existing' => [
        'passwordreset' => new oat\tao\model\entryPoint\PasswordReset(),
        'deliveryServer' => $this->getMockBuilder(Entrypoint::class)->getMock(),
        'guestaccess' => $this->getMockBuilder(Entrypoint::class)->getMock(),
        'proctoringDelivery' => $this->getMockBuilder(Entrypoint::class)->getMock(),
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
