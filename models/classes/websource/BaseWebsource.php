<?php
/**
 *
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
namespace oat\tao\model\websource;

use oat\oatbox\Configurable;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;
use League\Flysystem\FileNotFoundException;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\Stream;

/**
 * This is the base class of the Access Providers
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
abstract class BaseWebsource extends Configurable
implements Websource
{
    const OPTION_ID            = 'id';
    const OPTION_FILESYSTEM_ID = 'fsUri';
    
	/**
	 * Filesystem that is being made available
	 */
	protected $fileSystem = null;

	/**
	 * Identifier of the Access Provider 
	 * 
	 * @var string
	 */
	private $id;
	
	/**
	 * Used to instantiate new AccessProviders
	 * 
	 * @param string $fileSystem
	 * @param array $customConfig
	 * @return \tao_models_classes_fsAccess_AccessProvider
	 */
	protected static function spawn($fileSystemId, $customConfig = array()) {
	    $customConfig[self::OPTION_FILESYSTEM_ID] = $fileSystemId;
	    $customConfig[self::OPTION_ID] = uniqid();
	    $websource = new static($customConfig);
	    WebsourceManager::singleton()->addWebsource($websource);
	    return $websource;
	}

	/**
	 * Return the identifer of the AccessProvider
	 * 
	 * @return string
	 */
	public function getId() {
	    return $this->getOption(self::OPTION_ID);
	}

    /**
     * @return \League\Flysystem\Filesystem
     */
    public function getFileSystem()
    {
        if ($this->fileSystem === null) {
            /** @var FileSystemService $fsService */
            $fsService = ServiceManager::getServiceManager()->get(FileSystemService::SERVICE_ID);
            $this->fileSystem = $fsService->getFileSystem($this->getOption(self::OPTION_FILESYSTEM_ID));
        }
        return $this->fileSystem;
    }

    /**
     * @param $filePath
     * @throws \tao_models_classes_FileNotFoundException
     * @return StreamInterface
     */
    public function getFileStream($filePath)
    {
        if ($filePath === '') {
            throw new \tao_models_classes_FileNotFoundException("File not found");
        }
        $fs = $this->getFileSystem();
        try {
            $resource = $fs->readStream($filePath);
        } catch(FileNotFoundException $e) {
            throw new \tao_models_classes_FileNotFoundException("File not found");
        }
        return new Stream($resource, array('size' => $fs->getSize($filePath)));
    }

    /**
     * Get a file's mime-type.
     * @param string $filePath The path to the file.
     * @return string|false The file mime-type or false on failure.
     */
    public function getMimetype($filePath)
    {
        $mimeType = $this->getFileSystem()->getMimetype($filePath);

        $pathParts = pathinfo($filePath);
        if (isset($pathParts['extension'])) {
            //manage bugs in finfo
            switch ($pathParts['extension']) {
                case 'css':
                    //for css files mimetype can be 'text/plain' due to bug in finfo (see more: https://bugs.php.net/bug.php?id=53035)
                    if ($mimeType === 'text/plain' || $mimeType === 'text/x-asm') {
                        return $mimeType = 'text/css';
                    }
                    break;
                case 'svg':
                    if ($mimeType === 'text/plain') {
                        return $mimeType = 'image/svg+xml';
                    }
                    break;
            }
        }
        return $mimeType;
    }
}