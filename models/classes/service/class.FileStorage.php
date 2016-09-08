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

/**
 * Represents the file storage used in services 
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 
 */
class tao_models_classes_service_FileStorage extends ConfigurableService
{
    const SERVICE_ID = 'tao/ServiceFileStorage';
    
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
    
    public function import($id, $directoryPath) {
        $directory = $this->getDirectoryById($id);
        if (file_exists($directory->getPath())) {
            if(tao_helpers_File::isDirEmpty($directory->getPath())){
                common_Logger::d('Directory already found but content is empty');
                helpers_File::copy($directoryPath, $directory->getPath(), true);
                
            }else if (tao_helpers_File::isIdentical($directory->getPath(), $directoryPath)) {
                common_Logger::d('Directory already found but content is identical');
            } else {
                throw new common_Exception('Duplicate dir '.$id.' with different content');
            }
        } else {
            mkdir($directory->getPath(), 0700, true);
            helpers_File::copy($directoryPath, $directory->getPath(), true);
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
}