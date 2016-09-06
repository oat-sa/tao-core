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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */

use \oat\tao\model\websource\Websource;
use \League\Flysystem\Filesystem;
use \League\Flysystem\Adapter\Local;
use \oat\oatbox\filesystem\Directory;
use Psr\Http\Message\StreamInterface;

/**
 * Represents  directory for file storage
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class tao_models_classes_service_StorageDirectory extends Directory
{
    private $id;

    /** @var Websource */
    private $accessProvider;

    public function __construct($id, $filesystemId, $path, Websource $provider = null)
    {
        parent::__construct($filesystemId, $path);
        $this->id = $id;
        $this->accessProvider = $provider;
    }

    /**
     * @deprecated Should not be called
     *
     * @return Filesystem
     */
    public function getFlySystem()
    {
        return parent::getFilesystem();
    }

    /**
     * Returns the identifier of this directory
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Returns whenever or not this directory is public
     *
     * @return boolean
     */
    public function isPublic()
    {
        return !is_null($this->accessProvider);
    }
    
    /**
     * Returns a URL that allows you to access the files in a directory
     * preserving the relative paths
     * 
     * @return string
     * @throws common_Exception
     */
    public function getPublicAccessUrl()
    {
        if (is_null($this->accessProvider)) {
            common_Logger::e('accessss');
            throw new common_Exception('Tried obtaining access to private directory with ID ' . $this->getId());
        }
        return $this->accessProvider->getAccessUrl($this->prefix . DIRECTORY_SEPARATOR);
    }

    /**
     * @deprecated use $this->getPrefix instead
     * @return mixed|string
     */
    public function getRelativePath()
    {
        return $this->getPrefix();
    }

    /**
     * Returned the absolute path to this directory
     * Please use read and write to access files
     *
     * @deprecated
     * @return mixed
     * @throws common_exception_InconsistentData
     */
    public function getPath()
    {
        $adapter = $this->getFileSystem()->getAdapter();
        if (!$adapter instanceof Local) {
            throw new common_exception_InconsistentData(__CLASS__.' can only handle local files');
        }
        return $adapter->getPathPrefix() . $this->getPrefix();
    }

    /**
     * @deprecated use File->write instead
     *
     * @param $path
     * @param $string
     * @param null $mimeType
     * @return bool
     * @throws FileNotFoundException
     * @throws common_Exception
     */
    public function write($path, $string, $mimeType = null)
    {
        return $this->getFile($path)->write($string, $mimeType);
    }

    /**
     * @deprecated use File->write instead
     *
     * @param $path
     * @param $resource
     * @param null $mimeType
     * @return bool
     * @throws FileNotFoundException
     * @throws common_Exception
     */
    public function writeStream($path, $resource, $mimeType = null)
    {
        return $this->getFile($path)->write($resource, $mimeType);
    }

    /**
     * @deprecated use File->write instead
     *
     * @param $path
     * @param $stream
     * @param null $mimeType
     * @return bool
     * @throws FileNotFoundException
     * @throws common_Exception
     */
    public function writePsrStream($path, $stream, $mimeType = null)
    {
        return $this->getFile($path)->write($stream, $mimeType);
    }

    /**
     * @deprecated use File->read instead
     *
     * @param $path
     * @return false|string
     */
    public function read($path)
    {
        return $this->getFile($path)->read();
    }

    /**
     * @deprecated use File->readStream instead
     *
     * @param $path
     * @return false|resource
     */
    public function readStream($path)
    {
        return $this->getFile($path)->readStream();
    }

    /**
     * @deprecated use File->readPsrStream instead
     *
     * @param $path
     * @return StreamInterface
     */
    public function readPsrStream($path)
    {
        return $this->getFile($path)->readPsrStream();
    }

    /**
     * @deprecated use File->update instead
     *
     * @param $path
     * @param $content
     * @param null $mimeType
     * @return bool
     * @throws common_Exception
     */
    public function update($path, $content, $mimeType = null)
    {
       return $this->getFile($path)->update($content, $mimeType);
    }

    /**
     * @deprecated use File->update instead
     *
     * @param $path
     * @param $resource
     * @param null $mimeType
     * @return bool
     * @throws common_Exception
     */
    public function updateStream($path, $resource, $mimeType = null)
    {
        return $this->getFile($path)->update($resource, $mimeType);
    }

    /**
     * @deprecated use File->update instead
     *
     * @param $path
     * @param StreamInterface $stream
     * @param null $mimeType
     * @return bool
     * @throws common_Exception
     */
    public function updatePsrStream($path, StreamInterface $stream, $mimeType = null)
    {
        return $this->getFile($path)->update($stream, $mimeType);
    }

    /**
     * @deprecated use File->delete instead
     *
     * @param $path
     * @return bool
     */
    public function delete($path)
    {
        return $this->getFile($path)->delete();
    }

    /**
     * @deprecated use File->exists instead
     *
     * @param $path
     * @return bool
     */
    public function has($path)
    {
        return $this->getDirectory($path)->exists();
    }

    /**
     * @deprecated use $this->getFlyIterator instead
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $files = array();
        $iterator = $this->getFlyIterator(Directory::ITERATOR_FILE | Directory::ITERATOR_RECURSIVE);
        foreach ($iterator as $file) {
            $files[] = $this->getRelPath($file);
        }
        return new ArrayIterator($files);
    }
}