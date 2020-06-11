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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA ;
 */
declare(strict_types=1);

namespace oat\tao\helpers;

use tao_helpers_File;
use oat\oatbox\service\ConfigurableService;

/**
 * Wrapper on top of tao_helpers_File and some filesystem related functionality.
 *
 * Class FileHelperService
 * @package oat\tao\helpers
 */
class FileHelperService extends ConfigurableService
{
    /**
     * @param string $filePath
     * @return false|resource
     */
    public function readFile(string $filePath)
    {
        return fopen($filePath, 'r');
    }

    /**
     * @param resource $fileResource
     * @return bool
     */
    public function closeFile($fileResource): bool
    {
        if (!is_resource($fileResource)) {
            return false;
        }

        return fclose($fileResource);
    }

    /**
     * @param string $filePath
     * @return bool
     */
    public function removeFile(string $filePath): bool
    {
        return tao_helpers_File::remove($filePath, false);
    }

    /**
     * @param string $directoryPath
     * @return bool
     */
    public function removeDirectory(string $directoryPath): bool
    {
        return tao_helpers_File::remove($directoryPath, true);
    }
}
