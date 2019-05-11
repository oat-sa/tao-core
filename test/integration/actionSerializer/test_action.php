<?php

require_once __DIR__ . '/../../../includes/raw_start.php';

use oat\tao\model\actionSerializer\RdsActionSerializer;
use oat\oatbox\service\ServiceManager;

array_shift($argv);
$actionId = $argv[0];
$sleep = (integer) $argv[1];

$serializer = getInstance();
$serializer->lock($actionId);
sleep($sleep);
$serializer->unlock($actionId);

/**
 * @return RdsActionSerializer
 */
function getInstance()
{
    $service = new RdsActionSerializer([
        RdsActionSerializer::OPTION_PERSISTENCE => 'default'
    ]);
    $service->setServiceLocator(ServiceManager::getServiceManager());
    return $service;
}