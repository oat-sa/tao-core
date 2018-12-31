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
 */

namespace oat\tao\model\websource;

use GuzzleHttp\Psr7\Stream;
use League\Flysystem\FileNotFoundException;
use oat\oatbox\Configurable;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;
use Psr\Http\Message\StreamInterface;

/**
 * This is the base class of the Access Providers
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao

 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
abstract class BaseWebsource extends Configurable implements Websource
{
    const OPTION_ID = 'id';
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
     * @param $fileSystemId
     * @param array $customConfig
     * @return BaseWebsource
     * @throws \common_Exception
     */
    protected static function spawn($fileSystemId, $customConfig = array()) {
	    $customConfig[self::OPTION_FILESYSTEM_ID] = $fileSystemId;
	    $customConfig[self::OPTION_ID] = uniqid();
	    $webSource = new static($customConfig);
	    WebsourceManager::singleton()->addWebsource($webSource);

	    return $webSource;
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
     * @return null|\oat\oatbox\filesystem\FileSystem
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
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
     * @return StreamInterface
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     * @throws \tao_models_classes_FileNotFoundException
     */
    public function getFileStream($filePath)
    {
        if ($filePath === '') {
            throw new \tao_models_classes_FileNotFoundException("Empty file path");
        }
        $fs = $this->getFileSystem();
        try {
            $resource = $fs->readStream($filePath);
        } catch(FileNotFoundException $e) {
            throw new \tao_models_classes_FileNotFoundException($filePath);
        }
        return new Stream($resource, array('size' => $fs->getSize($filePath)));
    }

    /**
     * Get a file's mime-type.
     * @param string $filePath The path to the file.
     * @return string|false The file mime-type or false on failure.
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function getMimetype($filePath)
    {
        $mimeType = $this->getFileSystem()->getMimetype($filePath);

        $pathParts = pathinfo($filePath);
        if (isset($pathParts['extension'])) {
            //manage bugs in finfo
            switch ($pathParts['extension']) {
                case 'js':
                    if ($mimeType === 'text/plain' || $mimeType === 'text/x-asm' || $mimeType === 'text/x-c') {
                        return 'text/javascript';
                    }
                    break;
                case 'css':
                    //for css files mime type can be 'text/plain' due to bug in finfo (see more: https://bugs.php.net/bug.php?id=53035)
                    if ($mimeType === 'text/plain' || $mimeType === 'text/x-asm') {
                        return 'text/css';
                    }
                    break;
                case 'svg':
                    if ($mimeType === 'text/plain') {
                        return 'image/svg+xml';
                    }
                    break;
                case 'mp3':
                    return 'audio/mpeg';
                    break;
            }
        }

        return $mimeType;
    }
}
