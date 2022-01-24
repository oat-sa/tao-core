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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types = 1);

namespace oat\tao\model\config;

use oat\oatbox\filesystem\FileSystemService;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BackupConfigService implements BackupConfigServiceInterface
{
    public const FILE_SYSTEM_ID = 'config';

    protected const BACKUP_FILE_PREFIX = 'backup-';

    /** @var FileSystemService */
    private $fileSystemService;

    public function __construct(FileSystemService $fileSystemService)
    {
        $this->fileSystemService = $fileSystemService;
    }

    public function makeCopy(): void
    {
        $directory = $this->fileSystemService->getDirectory(self::FILE_SYSTEM_ID);

        $target = $directory->getFile($this->generateBackupFileName());
        $target->write(file_get_contents($this->archiveConfigDirectory(CONFIG_PATH)));
    }

    protected function generateBackupFileName(): string
    {
        return self::BACKUP_FILE_PREFIX . date('Ymd-His') . '.zip';
    }

    protected function archiveConfigDirectory(string $directory): string
    {
        $filename = tempnam(sys_get_temp_dir(), "zip");

        $zip = new ZipArchive();
        $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file)
        {
            if ($file->isDir()) {
                continue;
            }
            $filePath = $file->getRealPath();
            $zip->addFile($filePath, substr($filePath, strlen($directory)));
        }

        $zip->close();

        return $filename;
    }
}
