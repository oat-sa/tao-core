<?php
/*  
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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\tao\model\service\ApplicationService;

/**
 * This class provide the services for the Tao extension
 *
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 
 */
class tao_models_classes_TaoService
    extends tao_models_classes_GenerisService
{

    /**
     * The key to use to store the default TAO Upload File Source Repository URI
     * the TAO meta-extension configuration.
     *
     * @access public
     * @var string
     */
    const CONFIG_UPLOAD_FILESOURCE = 'defaultUploadFileSource';

    /**
     * Set the default file source for TAO File Upload.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  string sourceId The repository to be used as the default TAO File Upload Source.
     * @return void
     */
    public function setUploadFileSourceId($sourceId)
    {
        
    	$ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
    	$ext->setConfig(self::CONFIG_UPLOAD_FILESOURCE, $sourceId);
        
    }

    /**
     * Store an uploaded file in the persistant storage and return a serial id
     *
     * @param File $tmpFile
     * @param string $name
     * @return string
     */
    public function storeUploadedFile(File $tmpFile, $name = '')
    {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $fsId = $ext->getConfig(self::CONFIG_UPLOAD_FILESOURCE);
        $fss = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
        $baseDir = $fss->getDirectory($fsId);
        do {
            $unique = uniqid();
            $dir = implode('/',str_split(substr($unique, 0, 3))).'/'.substr($unique, 3);
            $file = $baseDir->getFile($dir.'/'.$name);
        } while ($file->exists());
        $file->write($tmpFile->read());
        $referencer = $this->getServiceLocator()->get(FileReferenceSerializer::SERVICE_ID);
        $serial = $referencer->serialize($file);
        return $serial;
    }

    /**
     * Get the tao platform version
     * @return string
     * @deprecated
     */
    public function getPlatformVersion()
    {
        return $this->getServiceLocator()->get(ApplicationService::SERVICE_ID)->getPlatformVersion();
    }

}
