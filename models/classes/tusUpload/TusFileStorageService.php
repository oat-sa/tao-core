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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\tusUpload;

use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\tusUpload\exception\ConnectionException;
use oat\tao\model\tusUpload\exception\FileException;
use oat\tao\model\tusUpload\exception\OutOfRangeException;

class TusFileStorageService extends ConfigurableService
{

    const FILESYSTEM_ID = 'tao';
    const STORAGE_NAME = 'tusUpload';
    const FILE_PREFIX = 'tus';


    /** @const Max chunk size */
    const CHUNK_SIZE = 8192; // 8 bytes.

    /** @const Input stream */
    const INPUT_STREAM = 'php://input';


    /** @const Append binary mode */
    const APPEND_BINARY = 'ab+';
    /** @const Read binary mode */
    const READ_BINARY = 'rb';


    /**
     *  Cache persistence
     */
    protected $cachePersistence;


    /**
     * @return FileSystemService|array
     */
    private function getFileSystemService()
    {
        return $this->getServiceLocator()
            ->get(FileSystemService::SERVICE_ID);
    }

    /**
     * @return Directory
     */
    private function getStorageDir()
    {
        return $this->getFileSystemService()
            ->getDirectory(self::FILESYSTEM_ID)
            ->getDirectory(self::STORAGE_NAME);

    }

    /**
     *
     */
    public function createStorage()
    {
        $this->getFileSystemService()
            ->createFileSystem(self::FILESYSTEM_ID)
            ->createDir(self::STORAGE_NAME);
    }

    /**
     * @param $path
     * @return string
     */
    public function getFilePath($path)
    {
        return $this->getStorageDir()->getPrefix() . $path;
    }


    /**
     * @param $cachePersistence
     */
    public function setCachePersistence($cachePersistence)
    {
        $this->cachePersistence = $cachePersistence;
    }

    /**
     * @return mixed
     */
    public function getCachePersistence()
    {
        return $this->cachePersistence;
    }

    /**
     * @param array $meta
     *
     * @return int
     * @throws ConnectionException
     *
     * Upload file to server.
     *
     */
    public function upload($meta)
    {
        if ($meta['offset'] === $meta['totalBytes']) {
            return $meta['offset'];
        }

        //open input stream and output file
        $input = $this->open(self::INPUT_STREAM, self::READ_BINARY);
        $output = $this->open($this->getFilePath($meta['fileName'] . $meta['key']), self::APPEND_BINARY);

        try {
            //check if file correct(if file smaller then offset). mode ab+ will write to the end of file
            $this->seek($output, $meta['offset']);

            while (!feof($input)) {
                if (CONNECTION_NORMAL !== connection_status()) {
                    throw new ConnectionException('Connection aborted by user.');
                }

                //read and write by chunks
                $data = $this->read($input, self::CHUNK_SIZE);
                $bytes = $this->write($output, $data, self::CHUNK_SIZE);

                //incrementing offsets
                $meta['offset'] += $bytes;

                //incrementing cache offset to be able resume correct uploading
                $this->updateCache($meta['key'], ['offset' => $meta['offset']]);

                //somehow system writes more bytes then file size.
                if ($meta['offset'] > $meta['totalBytes']) {
                    throw new OutOfRangeException('The uploaded file is corrupt.');
                }

                if ($meta['offset'] === $meta['totalBytes']) {
                    break;
                }
            }
        } finally {
            $this->close($input);
            $this->close($output);
        }

        return $meta['offset'];
    }

    /**
     *
     * @param string $filePath
     * @param string $mode
     *
     * @return resource
     * @throws FileException
     *
     * Open file in given mode.
     *
     */
    public function open(string $filePath, string $mode)
    {
        $ptr = @fopen($filePath, $mode);
        if (false === $ptr) {
            throw new FileException("Unable to open $filePath.");
        }
        return $ptr;
    }

    /**
     *
     * @param Resource $handle
     * @param int $offset
     * @param int $whence
     *
     * @return int
     * @throws FileException
     *
     * Move file pointer to given offset.
     * //Looks like useless. ab+ mode will start writing to the end of file
     *
     */
    public function seek($handle, int $offset, int $whence = SEEK_SET)
    {
        $position = fseek($handle, $offset, $whence);
        if (-1 === $position) {
            throw new FileException('Cannot move pointer to desired position.');
        }
        return $position;
    }

    /**
     * Read data from file.
     *
     * @param Resource $handle
     * @param int $chunkSize
     *
     * @return string
     * @throws FileException
     *
     */
    public function read($handle, int $chunkSize): string
    {
        $data = fread($handle, $chunkSize);
        if (false === $data) {
            throw new FileException('Cannot read file.');
        }
        return (string)$data;
    }

    /**
     *
     * @param Resource $handle
     * @param string $data
     * @param int|null $length
     *
     * @return int
     * @throws FileException
     *
     * Write data to file.
     *
     */
    public function write($handle, string $data, $length = null)
    {
        $bytesWritten = is_int($length) ? fwrite($handle, $data, $length) : fwrite($handle, $data);
        if (false === $bytesWritten) {
            throw new FileException('Cannot write to a file.');
        }
        return $bytesWritten;
    }

    /**
     * Close file.
     *
     * @param $handle
     *
     * @return bool
     */
    public function close($handle)
    {
        return fclose($handle);
    }

    /**
     * Update one key in cache
     * @param $updateKey
     * @param $data
     */
    protected function updateCache($updateKey, $data)
    {
        $cache = json_decode($this->getCachePersistence()->get($updateKey), true);
        foreach ($data as $key => $value) {
            $cache[$key] = $value;
        }
        $this->getCachePersistence()->set($updateKey, json_encode($cache));
    }
}
