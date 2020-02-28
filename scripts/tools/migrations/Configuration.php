<?php


namespace oat\tao\scripts\tools\migrations;

use Doctrine\Migrations\Configuration\Configuration as DoctrineConfiguration;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use DateTimeInterface;
use common_ext_Extension;
use Doctrine\Migrations\Exception\UnknownMigrationVersion;

class Configuration extends DoctrineConfiguration implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @var common_ext_Extension */
    private $extension;

    /**
     * @param common_ext_Extension $extension
     */
    public function setExtension(common_ext_Extension $extension)
    {
        $this->extension = $extension;
    }

    public function getMigrationsToExecute(string $direction, string $to) : array
    {
        $migrations = parent::getMigrationsToExecute($direction, $to);
        foreach ($migrations as $migration) {
            $migration->getMigration()->setServiceLocator($this->getServiceLocator());
        }
        return $migrations;
    }

    public function generateVersionNumber(?DateTimeInterface $now = null) : string
    {
        $version = parent::generateVersionNumber($now);
        if ($this->extension === null) {
            throw new UnknownMigrationVersion('Unknown extension');
        }
        $intHash = crc32($this->extension->getId());
        $intHash = substr($intHash,0,4);
        return $version.$intHash.'_'.$this->extension->getId();
    }
}