<?php

declare(strict_types=1);

namespace oat\tao\scripts\tools\migrations;

use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Migrations\Generator\ClassNameGenerator;
use common_ext_Extension as Extension;

class TaoClassNameGenerator extends ClassNameGenerator
{
    public const VERSION_FORMAT = 'YmdHis';
    private $extension;

    /**
     * TaoClassNameGenerator constructor.
     * @param Extension $extension
     */
    public function __construct(Extension $extension)
    {
        $this->extension = $extension;
    }

    public function generateClassName(string $namespace) : string
    {
        return $namespace . '\\Version' . $this->generateVersionNumber();
    }

    private function generateVersionNumber() : string
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $version = $now->format(self::VERSION_FORMAT);
        $intHash = (string) crc32($this->extension->getId());
        $intHash = substr($intHash,0,4);
        return $version.$intHash.'_'.$this->extension->getId();
    }
}
