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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\taskQueue\Task;

use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;

/**
 * Filesystem related functionalities in a task
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
trait FilesystemAwareTrait
{
    /**
     * @return FileSystemService
     */
    abstract protected function getFileSystemService();

    /**
     * Copies a locally stored file under filesystem of task queue storage for later use like:
     * - user downloading an export file
     * - saving a file for importing it later
     *
     * @param string $localFilePath
     * @param string|null $newFileName New name of the file under task queue filesystem
     * @return string File name (prefix) of the filesystem file
     */
    protected function saveFileToStorage($localFilePath, $newFileName = null)
    {
        if (!file_exists($localFilePath)) {
            return '';
        }

        if (null === $newFileName) {
            $newFileName = basename($localFilePath);
        }

        // saving the file under the storage
        $file = $this->getQueueStorage()->getFile($newFileName);
        $stream = fopen($localFilePath, 'r');
        $file->put($stream);
        fclose($stream);

        // delete the local file
        @unlink($localFilePath);

        return $newFileName;
    }

    /**
     * Writes arbitrary string data into a filesystem file under task queue storage.
     *
     * @param string $string
     * @param string $fileName
     * @return string
     *
     * @throws \League\Flysystem\FileExistsException
     * @throws \common_Exception
     */
    protected function saveStringToStorage($string, $fileName)
    {
        $file = $this->getQueueStorage()->getFile($fileName);

        $file->write((string) $string);

        return $file->getPrefix();
    }

    /**
     * @return  Directory
     */
    protected function getQueueStorage()
    {
        return $this->getFileSystemService()
            ->getDirectory(QueueDispatcherInterface::FILE_SYSTEM_ID);
    }

    /**
     * Deletes a filesystem file stored under task queue storage
     *
     * @param EntityInterface $taskLogEntity
     * @return bool
     */
    protected function deleteQueueStorageFile(EntityInterface $taskLogEntity)
    {
        if ($filename = $taskLogEntity->getFileNameFromReport()) {
            $file = $this->getQueueStorage()
                ->getFile($filename);

            if($file->exists()) {
                $file->delete();
            }
        }

        return false;
    }

    /**
     * Checks if the file exist in task queue storage.
     *
     * @param string $fileName
     * @return bool
     */
    protected function isFileStoredInQueueStorage($fileName)
    {
        if ($this->getQueueStorage()->getFile($fileName)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Tries to get the file if it exists.
     *
     * @param string $fileName
     * @return null|File
     */
    protected function getQueueStorageFile($fileName)
    {
        $file = $this->getQueueStorage()->getFile($fileName);

        if ($file->exists()) {
            return $file;
        }

        return null;
    }
}