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
    static protected $SESSION_ATTRIBUTE_FILES = 'tracked_uploads';
    static protected $SESSION_ATTRIBUTE_LOCAL = 'tracked_uploads_local_copies';


    /**
     * @param array $postedFile
     * @param string $folder
     * @return array
     */
    public function uploadFile(array $postedFile, $folder)
    {
        $targetLocation = tao_helpers_File::concat([$folder, uniqid('tmp', true) . $postedFile['name']]);
        $file = new File(self::$tmpFilesystemId, $targetLocation);
        $file->setServiceLocator($this->getServiceManager());
        $returnValue['uploaded'] = $file->put(fopen($postedFile['tmp_name'], 'rb'));
        $this->getServiceManager()->get(EventManager::CONFIG_ID)->trigger(new FileUploadedEvent($file));
        tao_helpers_File::remove($postedFile['tmp_name']);
        $data['type'] = $file->getMimetype();
        $data['uploaded_file'] = $this->getSerializer()->serialize($file);
        $data['name'] = $postedFile['name'];
        $data['size'] = $postedFile['size'];
        $returnValue['name'] = $postedFile['name'];
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
     * @throws \common_Exception
     */
    public function universalizeUpload($file)
    {
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            return $this->getSerializer()->unserializeFile($file);
        }
        if (is_string($file) && is_file($file)) {
            return $file;
        }

        throw new \common_Exception('Unsupported file reference');
    }

    /**
     * Either create local copy or use original location for tile
     * Returns absolute path to the file, to be compatible with legacy methods
     * @param string|File $serial
     * @return string
     * @throws \common_Exception
     */
    public function getUploadedFile($serial)
    {
        $file = $this->universalizeUpload($serial);
        if ($file instanceof File) {
            $file = $this->getLocalCopy($serial);
        }
        return $file;
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
        $session = \PHPSession::singleton();
        $storedFlyFiles = $session->hasAttribute(self::$SESSION_ATTRIBUTE_FILES) ? $session->getAttribute(self::$SESSION_ATTRIBUTE_FILES) : [];
        $storedFlyFiles[md5($event->getFile()->getPrefix())] = $event->getFile();
        $session->setAttribute(self::$SESSION_ATTRIBUTE_FILES, $storedFlyFiles);
    }

    /**
     * @param UploadLocalCopyCreatedEvent $event
     */
    public static function listenLocalCopyEvent(UploadLocalCopyCreatedEvent $event)
    {
        $session = \PHPSession::singleton();
        $storedLocalTmps = $session->hasAttribute(self::$SESSION_ATTRIBUTE_LOCAL) ? $session->getAttribute(self::$SESSION_ATTRIBUTE_LOCAL) : [];
        $storedLocalTmps = is_array($storedLocalTmps) ? $storedLocalTmps : [];
        $storedLocalTmps[$event->getTmpPath()] = md5($event->getFile()->getPrefix());
        $session->setAttribute(self::$SESSION_ATTRIBUTE_LOCAL, $storedLocalTmps);
    }

    public function remove($file)
    {
        $session = \PHPSession::singleton();
        $storedLocalTmps = $session->hasAttribute(self::$SESSION_ATTRIBUTE_LOCAL) ? $session->getAttribute(self::$SESSION_ATTRIBUTE_LOCAL) : [];
        $storedFlyFiles = $session->hasAttribute(self::$SESSION_ATTRIBUTE_FILES) ? $session->getAttribute(self::$SESSION_ATTRIBUTE_FILES) : [];


        if (is_string($file) && is_file($file)) {
            $hash = isset($storedLocalTmps[$file]) ? $storedLocalTmps[$file] : null;
            $file = isset($storedFlyFiles[$hash]) ? $storedFlyFiles[$hash] : null;
        }

        if ($file instanceof File) {
            $referencedHash = $this->getHash($file->getPrefix());

            foreach ($storedLocalTmps as $tmp => &$hash) {
                if ($hash === $referencedHash) {
                    tao_helpers_File::remove($tmp);
                    $hash = null;
                }
            }
            $file->delete();
            unset($storedFlyFiles[$referencedHash]);
        }

        $session->setAttribute(self::$SESSION_ATTRIBUTE_LOCAL, array_filter($storedLocalTmps));
        $session->setAttribute(self::$SESSION_ATTRIBUTE_FILES, array_filter($storedFlyFiles));

    }

    private function getHash($value)
    {
        return md5($value);
    }

}