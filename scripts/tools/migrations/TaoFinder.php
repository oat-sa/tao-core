<?php

declare(strict_types=1);

namespace oat\tao\scripts\tools\migrations;

use function glob;
use function rtrim;
use Doctrine\Migrations\Finder\Finder;

class TaoFinder extends Finder
{
    /**
     * @return string[]
     */
    public function findMigrations(string $directory, ?string $namespace = null) : array
    {
        $dir = $this->getRealPath($directory);
        $files = glob(rtrim($dir, '/') . '/*/migrations/Version*.php');
        return $this->loadMigrations($files, $namespace);
    }
}
