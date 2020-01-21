<?php


namespace oat\tao\model;


class Gateway
{
    public function __invoke($id = null)
    {
        if ($id) {
            return LegacySMStorage::getServiceManager()->get($id);
        }
        return LegacySMStorage::getServiceManager();
    }
}