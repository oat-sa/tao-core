<?php

namespace oat\tao\model\di;


use Symfony\Component\DependencyInjection\Container as BaseContainer;
use Zend\ServiceManager\ServiceLocatorInterface;

class Container extends BaseContainer implements ServiceLocatorInterface
{

    /** Prevents PDO connection serialization on session close @FIX */
    public function __sleep()
    {
        return [];
    }

}
