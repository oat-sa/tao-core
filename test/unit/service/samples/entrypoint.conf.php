<?php
/**
 * Default config header created during install
 */
use oat\tao\model\entryPoint\Entrypoint;

return new oat\tao\model\entryPoint\EntryPointService(array(
    'existing' => array(
        'passwordreset' => new oat\tao\model\entryPoint\PasswordReset(),
        'deliveryServer' => $this->getMockBuilder(Entrypoint::class)->getMock(),
        'guestaccess' => $this->getMockBuilder(Entrypoint::class)->getMock(),
        'proctoringDelivery' => $this->getMockBuilder(Entrypoint::class)->getMock(),
    ),
    'postlogin' => array(
        'deliveryServer',
        'backoffice',
        'proctoring',
        'childOrganization',
        'scoreReport',
        'exam',
        'testingLocationList',
        'proctoringDelivery'
    ),
    'prelogin' => array(
        'guestaccess',
        'proctoringDelivery'
    ),
    'new_tag' => [
        'proctoringDelivery'
    ]
));
