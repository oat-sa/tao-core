<?php

declare(strict_types=1);

namespace oat\tao\scripts\tools\migrations;

use function glob;
use function rtrim;
use Doctrine\Migrations\Finder\Finder;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use common_ext_ExtensionsManager as ExtensionsManager;

class TaoFinder extends Finder implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @return string[]
     */
    public function findMigrations(string $directory, ?string $namespace = null) : array
    {
        $dir = rtrim($this->getRealPath($directory), '\\/');
        $root = rtrim($this->getRealPath(ROOT_PATH), '\\/');
        if ($dir === $root) {
            //global search
            $pattern = $dir . '/*/migrations/Version*.php';
        } else {
            //search by extension
            $pattern = $dir . '/migrations/Version*.php';
        }
        $files = glob($pattern);
        $files = array_filter($files, [$this, 'filterMigration']);
        return $this->loadMigrations($files, $namespace);
    }

    /**
     * @param $filePath
     * @return bool
     */
    private function filterMigration(string $filePath): bool
    {
        $result = false;
        preg_match('/_([a-zA-Z0-9]+)\.php$/', $filePath, $matches);
        /** @var ExtensionsManager $extManager */
        $extManager = $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);
        if (isset($matches[1])) {
            $result = $extManager->isInstalled($matches[1]);
        }
        return $result;
    }
}
