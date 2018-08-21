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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\model\upload;


use oat\generis\model\fileReference\UrlFileSerializer;
use oat\oatbox\event\EventManager;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\event\FileUploadedEvent;
use oat\tao\model\event\UploadLocalCopyCreatedEvent;
use tao_helpers_File;

class UploadService extends ConfigurableService
{
    const SERVICE_ID = 'tao/upload';

    static public $tmpFilesystemId = 'sharedTmp';
    private $uriSerializer;


    /**
     * @param array $postedFile
     * @param string $folder
     * @return array
     * @throws \InvalidArgumentException
     * @throws \common_Exception
     */
    public function uploadFile(array $postedFile, $folder)
    {
        $tmp_name = array_key_exists('tmp_name', $postedFile) ? $postedFile['tmp_name'] : null;

        if (!$tmp_name) {
            throw  new \InvalidArgumentException('Upload filename is missing');
        }
        $name = array_key_exists('name', $postedFile) ? $postedFile['name'] : uniqid('unknown_', false);
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        $targetName     = uniqid('tmp', true) . '.' . $extension;
        $targetLocation = tao_helpers_File::concat([$folder, $targetName]);

        $file = $this->getUploadDir()->getFile($this->getUserDirectoryHash() . $targetLocation);

        $returnValue['uploaded'] = $file->put(fopen($tmp_name, 'rb'));
        $this->getServiceManager()->get(EventManager::CONFIG_ID)->trigger(new FileUploadedEvent($file));
        tao_helpers_File::remove($tmp_name);

        $data['type'] = $file->getMimetype();
        $data['uploaded_file'] = $file->getBasename();
        $data['name'] = $name;
        $data['size'] = array_key_exists('size', $postedFile) ? $postedFile['size'] : $file->getSize();
        $returnValue['name'] = $name;
        $returnValue['uploaded_file'] = $data['uploaded_file'];
        $returnValue['data'] = json_encode($data);

        return $returnValue;
    }

    /**
     * Returns the file system identifier.
     *
     * @return string
     */
    public function getUploadFSid()
    {
        return self::$tmpFilesystemId;
    }

    /**
     * Returns the current file system directory instance.
     *
     * @return Directory
     */
    public function getUploadDir()
    {
        return $this->getServiceLocator()->get(FileSystemService::SERVICE_ID)->getDirectory($this->getUploadFSid());
    }
    /**
     *
     * @return \oat\generis\model\fileReference\UrlFileSerializer
     */
    public function getSerializer()
    {
        if (!$this->uriSerializer) {
            $this->uriSerializer = new UrlFileSerializer();
            $this->uriSerializer->setServiceLocator($this->getServiceLocator());
        }
        return $this->uriSerializer;
    }

    /**
     * @param $serial
     * @return null|File
     * @throws \common_exception_Error
     * @throws \common_exception_NotAcceptable
     */
    public function getUploadedFlyFile($serial)
    {
        $path = $this->getUserDirectoryHash() . '/';
        if (filter_var($serial, FILTER_VALIDATE_URL)) {
            // Getting the File instance of the file url.
            $fakeFile = $this->getSerializer()->unserializeFile($serial);

            // Filesystem hack check.
            if ($fakeFile->getFileSystemId() !== $this->getUploadFSid()) {
                throw new \common_exception_NotAcceptable(
                    'The uploaded file url contains a wrong filesystem id!' .
                    '(Expected: `' . $this->getUploadFSid() . '` Actual: `' . $fakeFile->getFileSystemId() . '`)'
                );
            }

            $path .= $fakeFile->getBasename();
        }
        else {
            $path .= $serial;
        }

        $realFile = $this->getUploadDir()->getFile($path);
        if ($realFile->exists()) {
            return $realFile;
        }

        return null;
    }

    /**
     * Remove an uploaded file
     *
     * @param $file
     */
    public function remove($file)
    {
        if ($file instanceof File) {
            $file->delete();
        }
    }

    /**
     * Returns the username directory hash.
     *
     * @return string
     * @throws \common_exception_Error
     */
    public function getUserDirectoryHash()
    {
        return hash(
            'crc32b',
            \common_session_SessionManager::getSession()->getUser()->getIdentifier()
        );
    }

    /**
     * Is the uploaded filename looks as an uploaded file.
     *
     * @param $filePath
     * @return bool
     * @throws \common_exception_Error
     */
    public function isUploadedFile($filePath)
    {
        // If it's a serialized one.
        if (filter_var($filePath, FILTER_VALIDATE_URL)) {
            return true;
        }

        // If it's a normal filename.
        $file = $this->getUploadDir()->getFile($this->getUserDirectoryHash() . '/' . $filePath);
        if ($file->exists()) {
            return true;
        }

        return false;
    }

}