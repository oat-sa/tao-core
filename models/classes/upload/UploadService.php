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
use oat\oatbox\filesystem\File;
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

        $targetLocation = tao_helpers_File::concat([$folder, uniqid('tmp', true) . $name]);
        $file = new File(self::$tmpFilesystemId, $targetLocation);
        $file->setServiceLocator($this->getServiceManager());

        $returnValue['uploaded'] = $file->put(fopen($tmp_name, 'rb'));
        $this->getServiceManager()->get(EventManager::CONFIG_ID)->trigger(new FileUploadedEvent($file));
        tao_helpers_File::remove($tmp_name);


        $data['type'] = $file->getMimetype();
        $data['uploaded_file'] = $this->getSerializer()->serialize($file);
        $data['name'] = $name;
        $data['size'] = array_key_exists('size', $postedFile) ? $postedFile['size'] : $file->getSize();
        $returnValue['name'] = $name;
        $returnValue['uploaded_file'] = $data['uploaded_file'];
        $returnValue['data'] = json_encode($data);

        return $returnValue;
    }

    public function getUploadFSid()
    {
        return self::$tmpFilesystemId;
    }

    /**
     *
     * @return \oat\generis\model\fileReference\UrlFileSerializer
     */
    protected function getSerializer()
    {
        if (!$this->uriSerializer) {
            $this->uriSerializer = new UrlFileSerializer();
            $this->uriSerializer->setServiceLocator($this->getServiceLocator());
        }
        return $this->uriSerializer;
    }

    /**
     * Detects
     * @param $file
     * @return File|string
     * @deprecated
     * @throws \common_Exception
     */
    public function universalizeUpload($file)
    {
        if (is_string($file) && is_file($file)) {
            return $file;
        }

        return $this->getUploadedFlyFile($file);
    }

    /**
     * Either create local copy or use original location for tile
     * Returns absolute path to the file, to be compatible with legacy methods
     * @deprecated
     * @param string|File $serial
     * @return string
     * @throws \common_Exception
     */
    public function getUploadedFile($serial)
    {
        $file = $this->universalizeUpload($serial);
        if ($file instanceof File) {
            $file = $this->getLocalCopy($file);
        }
        return $file;
    }

    /**
     * @param string $serial
     * @return File
     */
    public function getUploadedFlyFile($serial)
    {
        if (filter_var($serial, FILTER_VALIDATE_URL)) {
            return $this->getSerializer()->unserializeFile($serial);
        }
        return null;
    }

    /**
     * Deprecated, for compatibility with legacy code
     * @param $file
     * @return string
     * @throws \common_Exception
     */
    private function getLocalCopy(File $file)
    {
        $tmpName = \tao_helpers_File::concat([\tao_helpers_File::createTempDir(), $file->getPrefix()]);
        if (($resource = fopen($tmpName, 'wb')) !== false) {
            stream_copy_to_stream($file->readStream(), $resource);
            fclose($resource);
            $this->getServiceLocator()->get(EventManager::CONFIG_ID)->trigger(new UploadLocalCopyCreatedEvent($file,
                $tmpName));
            return $tmpName;
        }
        throw new \common_Exception('Impossible to make local file copy at ' . $tmpName);
    }

    /**
     * @param FileUploadedEvent $event
     */
    public static function listenUploadEvent(FileUploadedEvent $event)
    {
        $storage = TempFlyStorageAssociation::getStorage();
        $storage->setUpload($event->getFile());
    }

    /**
     * @param UploadLocalCopyCreatedEvent $event
     */
    public static function listenLocalCopyEvent(UploadLocalCopyCreatedEvent $event)
    {
        $storage = TempFlyStorageAssociation::getStorage();
        $storage->addLocalCopies($event->getFile(), $event->getTmpPath());
    }

    public function remove($file)
    {
        $storage = TempFlyStorageAssociation::getStorage();

        if ($file instanceof File) {

            $storedLocalTmps = $storage->getLocalCopies($file);
            foreach ((array)$storedLocalTmps as $tmp) {
                tao_helpers_File::remove($tmp);
            }
            $storage->removeFiles($file);
            $file->delete();
        }
    }

}