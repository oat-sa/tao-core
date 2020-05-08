<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 *
 */

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

    /**
     * @param string $namespace
     * @return string
     */
    public function generateClassName(string $namespace) : string
    {
        return $namespace . '\\Version' . $this->generateVersionNumber();
    }

    /**
     * @return string
     */
    private function generateVersionNumber() : string
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $version = $now->format(self::VERSION_FORMAT);
        $intHash = (string) crc32($this->extension->getId());
        $intHash = substr($intHash,0,4);
        return $version.$intHash.'_'.$this->extension->getId();
    }
}
