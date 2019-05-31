<?php
/**
 * Default config header created during install
 */
use oat\tao\model\entryPoint\Entrypoint;

class EntrypointMock1 implements Entrypoint
{

    public function getId() {
        return 'EntrypointMock1';
    }

    public function getTitle() {
        return __("EntrypointMock1 titile");
    }

    public function getLabel() {
        return __('EntrypointMock1 label');
    }

    public function getDescription() {
        return __('EntrypointMock1 description');
    }

    public function getUrl() {
        return _url('index', 'EntrypointMock1', 'tao');
    }
}

class EntrypointMock2 implements Entrypoint
{

    public function getId() {
        return 'EntrypointMock2';
    }

    public function getTitle() {
        return __("EntrypointMock2 titile");
    }

    public function getLabel() {
        return __('EntrypointMock2 label');
    }

    public function getDescription() {
        return __('EntrypointMock2 description');
    }

    public function getUrl() {
        return _url('index', 'EntrypointMock2', 'tao');
    }
}

return new oat\tao\model\entryPoint\EntryPointService(array(
    'existing' => array(
        'passwordreset' => new oat\tao\model\entryPoint\PasswordReset(),
        'deliveryServer' => new EntrypointMock1(),
        'guestaccess' => new oat\taoDeliveryRdf\model\guest\GuestAccess(),
        'proctoringDelivery' => new EntrypointMock2(),
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
