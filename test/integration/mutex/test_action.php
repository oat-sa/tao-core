<?php

require_once __DIR__ . '/../../../includes/raw_start.php';

use oat\tao\model\mutex\LockService;
use oat\oatbox\service\ServiceManager;

array_shift($argv);
$actionId = $argv[0];
$sleep = (integer) $argv[1];
$timeout = (integer) $argv[2];

$service = getInstance();
$factory = $service->getLockFactory();
$lock = $factory->createLock($actionId, $timeout);
$lock->acquire(true);
sleep($sleep);
$lock->release();

/**
 * @return LockService
 */
function getInstance()
{
    $service = new LockService([
        LockService::OPTION_PERSISTENCE => 'default'
    ]);
    $service->setServiceLocator(ServiceManager::getServiceManager());
    return $service;
}