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
 * Copyright (c) 2013-2025 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\websource;

use common_Exception;
use common_exception_Error;
use common_exception_NotFound;
use GuzzleHttp\Psr7\Stream;
use oat\oatbox\Configurable;
use oat\oatbox\filesystem\FileSystem;
use oat\oatbox\filesystem\FilesystemException;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;
use Psr\Http\Message\StreamInterface;
use tao_helpers_File;
use tao_models_classes_FileNotFoundException;

/**
 * @author Joel Bout, <joel@taotesting.com>
 */
abstract class BaseWebsource extends Configurable implements Websource
{
    public const OPTION_ID = 'id';
    public const OPTION_FILESYSTEM_ID = 'fsUri';
    private const ALLOWED_SVGZ_MIMETYPES = ['text/plain', 'image/svg', 'application/x-gzip'];

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
     * @throws common_Exception
     */
    protected static function spawn($fileSystemId, $customConfig = [])
    {
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
    public function getId()
    {
        return $this->getOption(self::OPTION_ID);
    }

    /**
     * @return null|FileSystem
     * @throws common_exception_Error
     * @throws common_exception_NotFound
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
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws tao_models_classes_FileNotFoundException
     */
    public function getFileStream($filePath)
    {
        if ($filePath === '') {
            throw new tao_models_classes_FileNotFoundException("Empty file path");
        }
        $fs = $this->getFileSystem();
        try {
            $resource = $fs->readStream($filePath);
        } catch (FilesystemException $e) {
            throw new tao_models_classes_FileNotFoundException($filePath);
        }
        return new Stream($resource, ['size' => $fs->fileSize($filePath)]);
    }

    /**
     * Get a file's mime-type.
     * @param string $filePath The path to the file.
     * @return string|false The file mime-type or false on failure.
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws FilesystemException
     */
    public function getMimetype($filePath)
    {
        $mimeType = $this->getFileSystem()->mimeType($filePath);

        $pathParts = pathinfo($filePath);
        if (!isset($pathParts['extension'])) {
            return $mimeType;
        }

        switch ($pathParts['extension']) {
            case 'js':
                if (str_starts_with($mimeType, 'text/')) {
                    return 'text/javascript';
                }
                break;
            case 'css':
                // for css files mime type can be 'text/plain' due to bug in finfo
                // (see more: https://bugs.php.net/bug.php?id=53035)
                if (str_starts_with($mimeType, 'text/')) {
                    return 'text/css';
                }
                break;
            case 'svg':
            case 'svgz':
                // when there are more than one image in svg file - finfo recognizes it as `image/svg`, while it
                // should be `image/svg+xml` or at least `text/plain` for a previous hack to work
                if (in_array($mimeType, self::ALLOWED_SVGZ_MIMETYPES, true)) {
                    return tao_helpers_File::MIME_SVG;
                }
                break;
            case 'mp3':
                return 'audio/mpeg';
        }

        return $mimeType;
    }
}
