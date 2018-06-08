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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

use oat\tao\model\websource\WebsourceManager;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\websource\Websource;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\service\ServiceFileStorage;

/**
 * Represents the file storage used in services 
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 
 */
class tao_models_classes_service_FileStorage extends ConfigurableService implements ServiceFileStorage
{
    const OPTION_PUBLIC_FS = 'public';
    const OPTION_PRIVATE_FS = 'private';
    const OPTION_ACCESS_PROVIDER = 'provider';
    
    /**
     * @return tao_models_classes_service_FileStorage
     */
    public static function singleton() {
        return ServiceManager::getServiceManager()->get(self::SERVICE_ID);
    }
    
    private $accessProvider;

    /**
     * @return string
     */
    protected function getFsId($public)
    {
        return $public ? $this->getOption(self::OPTION_PUBLIC_FS) : $this->getOption(self::OPTION_PRIVATE_FS);
    }

    /**
     * @return Websource
     * @throws \oat\tao\model\websource\WebsourceNotFound
     */
    protected function getAccessProvider()
    {
        if (is_null($this->accessProvider)) {
            $this->accessProvider = WebsourceManager::singleton()->getWebsource($this->getOption(self::OPTION_ACCESS_PROVIDER));
        }
        return $this->accessProvider;
    }
    
    /**
     * @param boolean $public
     * @return tao_models_classes_service_StorageDirectory
     */
    public function spawnDirectory($public = false) {
        $id = common_Utils::getNewUri().($public ? '+' : '-');
        $directory = $this->getDirectoryById($id);
        return $directory;
    }

    /**
     * @param string $id
     * @return tao_models_classes_service_StorageDirectory
     */
    public function getDirectoryById($id) {
        $public = $id[strlen($id)-1] == '+';
        $path = $this->id2path($id);
        $dir = new tao_models_classes_service_StorageDirectory(
            $id,
            $this->getFsId($public),
            $path,
            $public ? $this->getAccessProvider() : null
        );
        $dir->setServiceLocator($this->getServiceLocator());
        return $dir;
    }

    /**
     * Delete directory represented by the $id
     *
     * @param $id
     * @return mixed
     */
    public function deleteDirectoryById($id)
    {
        $public = $id[strlen($id)-1] == '+';
        $path = $this->id2path($id);
        return $this->getServiceLocator()->get(FileSystemService::SERVICE_ID)->getFileSystem($this->getFsId($public))->deleteDir($path);
    }

    /**
     * @param string $id
     * @param string $directoryPath
     * @throws common_Exception
     */
    public function import($id, $directoryPath)
    {
        $directory = $this->getDirectoryById($id);
        if (is_dir($directoryPath) && is_readable($directoryPath)) {
            foreach (
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST) as $item
            ) {
                if (!$item->isDir()) {
                    $file = $directory->getFile($iterator->getSubPathName());
                    $fh = fopen($item, 'rb');

                    if ($file->exists()) {
                        if (0 !== strcmp($this->getStreamHash($fh), $this->getStreamHash($file->readStream()))) {
                            fclose($fh);
                            throw new common_Exception('Different file content');
                        }
                    } else {
                        $file->put($fh);
                        fclose($fh);
                    }
                }
            }
        } else {
            common_Logger::w('Missing directory ' . $directoryPath);
        }
    }
    
    private function id2path($id) {

        $encoded = md5($id);
        $returnValue = "";
        $len = strlen($encoded);
        for ($i = 0; $i < $len; $i++) {
            if ($i < 3) {
                $returnValue .= $encoded[$i].DIRECTORY_SEPARATOR;
            } else {
                $returnValue .= $encoded[$i];
            }
        }
        
        return $returnValue.DIRECTORY_SEPARATOR;
    }

    /**
     * Calculates hash for given stream
     * @param $stream
     * @param string $hash
     * @return string
     */
    private function getStreamHash($stream, $hash = 'md5')
    {
        $hc = hash_init($hash);
        hash_update_stream($hc, $stream);
        return hash_final($hc);
    }
}