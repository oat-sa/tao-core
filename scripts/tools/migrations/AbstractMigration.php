<?php


namespace oat\tao\scripts\tools\migrations;

use Doctrine\Migrations\AbstractMigration as DoctrineAbstractMigration;
use oat\oatbox\service\ServiceManagerAwareInterface;
use oat\oatbox\service\ServiceManagerAwareTrait;

abstract class AbstractMigration extends DoctrineAbstractMigration implements ServiceManagerAwareInterface
{
    use ServiceManagerAwareTrait;
}