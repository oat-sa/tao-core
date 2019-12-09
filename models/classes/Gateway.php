<?php


namespace oat\tao\model;


use oat\oatbox\service\ServiceManager;

class Gateway
{
    public function __invoke($id = null)
    {
        if ($id) {
            return ServiceManager::getServiceManager()->get($id);
        }
        return ServiceManager::getServiceManager();
    }
}