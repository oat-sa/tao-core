<?php

namespace oat\tao\model;


use oat\oatbox\service\ServiceManager;

class LegacySMStorage
{

    /**
     * @var ServiceManager
     */
    private static $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function setServiceManager(ServiceManager $serviceManager): void
    {
        self::$instance = $serviceManager;
    }

    public static function getServiceManager(): ?ServiceManager
    {
        return self::$instance;
    }

}
