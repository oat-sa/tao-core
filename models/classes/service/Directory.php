<?php

namespace oat\tao\model\service;

use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\StreamWrapper;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Http\Message\StreamInterface;

class Directory implements \IteratorAggregate
{
    /**
     * Registered directory from FileSystemService::SERVICE_ID
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Relative path inside $this->filesystem directory
     *
     * @var string
     */
    protected $path;

    /**
     * Directory constructor.
     *
     * @param Filesystem $filesystem
     * @param $path
     */
    public function __construct(Filesystem $filesystem, $path = '.')
    {
        $this->filesystem = $filesystem;
        $this->path = $path;
    }

    /**
     * Get the relative path inside $this->filesystem
     *
     * @return string
     */
    public function getRelativePath()
    {
        return $this->sanitizePath($this->path);
    }

    /**
     * @return Filesystem
     */
    protected function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Sanitize path by removing "\" (Window compatibility)
     *
     * @param $path
     * @return mixed
     */
    protected function sanitizePath($path)
    {
        if (!$this->getFileSystem()->getAdapter() instanceof Local) {
            $path = str_replace('\\', '/', $path);
        }
        return $path;
    }

    /**
     * Return the path concatenated with $this->path
     *
     * @param $path
     * @return mixed
     */
    protected function getFullPath($path)
    {
        $path = rtrim($this->getRelativePath(), '\\/') . '/' . ltrim($path, '\\/');
        $path = $this->sanitizePath($path);
        return $path;
    }

    /**
     * Return content of file located at $path. Output as string
     *
     * @param $path
     * @return false|string
     */
    public function read($path)
    {
        return $this->getFileSystem()->read($this->getFullPath($path));
    }

    /**
     * Return content of file located at $path. Output as PHP stream
     *
     * @param $path
     * @return false|resource
     */
    public function readStream($path)
    {
        return $this->getFileSystem()->readStream($this->getFullPath($path));
    }

    /**
     * Return content of file located at $path. Output as PSR-7 stream
     *
     * @param $path
     * @return Stream
     */
    public function readPsrStream($path)
    {
        $path = $this->sanitizePath($path);
        return new Stream($this->readStream($path));
    }

    /**
     * Store a file in the directory from string
     *
     * @param $path
     * @param $content
     * @param null $mimeType
     * @return bool
     */
    public function write($path, $content, $mimeType = null)
    {
        if (! is_string($content)) {
            throw new \InvalidArgumentException(__FUNCTION__ . ' expected content as valid string.');
        }

        $path = $this->getFullPath($path);
        \common_Logger::i($path);
        \common_Logger::d('Writting in ' . $path);
        $config = (is_null($mimeType)) ? [] : ['ContentType' => $mimeType];
        return $this->getFileSystem()->write($path, $content, $config);
    }

    /**
     * Store a file in the directory from PHP stream
     *
     * @param $path
     * @param $resource
     * @param null $mimeType
     * @return bool
     */
    public function writeStream($path, $resource, $mimeType = null)
    {
        if (! is_resource($resource)) {
            throw new \InvalidArgumentException(__FUNCTION__ . ' expected content as valid resource.');
        }

        $path = $this->getFullPath($path);
        \common_Logger::d('Writting in ' . $path);
        $config = (is_null($mimeType)) ? [] : ['ContentType' => $mimeType];
        return $this->getFileSystem()->writeStream($path, $resource, $config);
    }

    /**
     * Store a file in the directory from PSR-7 stream
     *
     * @param $path
     * @param StreamInterface $stream
     * @param null $mimeType
     * @return bool
     * @throws \common_Exception
     */
    public function writePsrStream($path, StreamInterface $stream, $mimeType = null)
    {
        $path = $this->sanitizePath($path);
        if (!$stream->isReadable()) {
            throw new \common_Exception('Stream is not readable. Write to filesystem aborted.');
        }
        if (!$stream->isSeekable()) {
            throw new \common_Exception('Stream is not seekable. Write to filesystem aborted.');
        }
        $stream->rewind();

        $resource = StreamWrapper::getResource($stream);
        if (! is_resource($resource)) {
            throw new \common_Exception('Unable to create resource from the given stream. Write to filesystem aborted.');
        }

        return $this->writeStream($path, $resource, $mimeType);
    }

    /**
     * Update an existing file in the directory from string
     *
     * @param $path
     * @param $content
     * @param null $mimeType
     * @return bool
     * @throws \tao_models_classes_FileNotFoundException
     */
    public function update($path, $content, $mimeType = null)
    {
        if (! is_string($content)) {
            throw new \InvalidArgumentException(__FUNCTION__ . ' expected content as valid string.');
        }

        if (! $this->hasFile($path)) {
            throw new \tao_models_classes_FileNotFoundException('File "' . $this->getFullPath($path) . ' not found for update.');
        }

        $path = $this->getFullPath($path);
        \common_Logger::d('Updating file ' . $path);
        $config = (is_null($mimeType)) ? [] : ['ContentType' => $mimeType];

        return $this->getFileSystem()->update($path, $content, $config);
    }

    /**
     * Update an existing  file in the directory from PHP stream
     *
     * @param $path
     * @param $resource
     * @param null $mimeType
     * @return bool
     * @throws \tao_models_classes_FileNotFoundException
     */
    public function updateStream($path, $resource, $mimeType = null)
    {
        if (! is_resource($resource)) {
            throw new \InvalidArgumentException(__FUNCTION__ . ' expected content as valid resource.');
        }

        if (! $this->hasFile($path)) {
            throw new \tao_models_classes_FileNotFoundException('File "' . $this->getFullPath($path) . ' not found for update.');
        }

        $path = $this->getFullPath($path);
        \common_Logger::d('Updating file ' . $path);
        $config = (is_null($mimeType)) ? [] : ['ContentType' => $mimeType];
        return $this->getFileSystem()->updateStream($path, $resource, $config);
    }

    /**
     * Update an existing file in the directory from PSR-7 stream
     *
     * @param $path
     * @param StreamInterface $stream
     * @param null $mimeType
     * @return bool
     * @throws \common_Exception
     */
    public function updatePsrStream($path, StreamInterface $stream, $mimeType = null)
    {
        $path = $this->sanitizePath($path);
        if (!$stream->isReadable()) {
            throw new \common_Exception('Stream is not readable. Update to filesystem aborted.');
        }
        if (!$stream->isSeekable()) {
            throw new \common_Exception('Stream is not seekable. Update to filesystem aborted.');
        }
        $stream->rewind();

        $resource = StreamWrapper::getResource($stream);
        if (! is_resource($resource)) {
            throw new \common_Exception('Unable to create resource from the given stream. Update to filesystem aborted.');
        }

        return $this->updateStream($path, $resource, $mimeType);
    }

    /**
     * Check if file or directory exists
     *
     * @param $path
     * @return bool
     */
    public function has($path)
    {
        return $this->getFileSystem()->has($this->getFullPath($path));
    }

    /**
     * Check if directory exists
     *
     * @param $path
     * @return bool
     */
    public function hasDirectory($path)
    {
        if ($this->has($path)) {
            $metadata = $this->getFilesystem()->getMetadata($this->getFullPath($path));
            return (boolean) ($metadata['type'] !== 'file');
        }
        return false;
    }

    /**
     * Check if file exists
     *
     * @param $path
     * @return bool
     */
    public function hasFile($path)
    {
        if ($this->has($path)) {
            $metadata = $this->getFilesystem()->getMetadata($this->getFullPath($path));
            return (boolean) ($metadata['type'] === 'file');
        }
        return false;
    }

    /**
     * Delete file
     *
     * @param $path
     * @return bool
     * @throws \tao_models_classes_FileNotFoundException
     */
    public function delete($path)
    {
        try {
            return $this->getFileSystem()->delete($this->getFullPath($path));
        } catch (\FileNotFoundException $e) {
            \common_Logger::e($e->getMessage());
            throw new \tao_models_classes_FileNotFoundException($path);
        }
    }

    /**
     * Return an iterator which handle flat list of file
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $files = array();
        $content = $this->getFileSystem()->listContents($this->getRelativePath(), true);
        foreach ($content as $file) {
            if ($file['type'] === 'file') {
                $files[] = str_replace($this->getRelativePath(), '', $file['path']);
            }
        }
        return new \ArrayIterator($files);
    }

    /**
     * Return an iterator which handle list of file with relative path
     *
     * @return \ArrayIterator
     */
    public function getIteratorWithRelativePath()
    {
        $files = array();
        $content = $this->getFileSystem()->listContents($this->getRelativePath(), true);
        \common_Logger::i($this->getRelativePath());
        foreach ($content as $file) {
            if ($file['type'] === 'file') {
                $files[] = $file['path'];
            }
        }
        return new \ArrayIterator($files);
    }

    /**
     * Return an iterator which handle list of directory
     *
     * @param $path
     * @return \ArrayIterator
     * @throws \common_Exception
     */
    public function getDirectoryIterator($path = null)
    {
        if (is_null($path)) {
            $path = $this->path;
        } else {
            if (! $this->hasDirectory($path)) {
                throw new \common_Exception('Directory iterator needs a valid directory.');
            }
            $path = $this->getFullPath($path);
        }

        $files = array();
        $content = $this->getFileSystem()->listContents($path, false);
        foreach ($content as $file) {
            if (! in_array($file['path'], array('.','..'))) {
                $files[] = str_replace($this->getRelativePath(), '', $file['path']);
            }
        }

        return new \ArrayIterator($files);
    }

    /**
     * If found return the directory at $path location
     *
     * @param $path
     * @return Directory
     * @throws \tao_models_classes_FileNotFoundException
     */
    public function getDirectory($path)
    {
        if (! $this->hasDirectory($path)) {
            throw new \tao_models_classes_FileNotFoundException('Directory "' . $path . '" not found '
                . 'into directory "' . $this->getRelativePath() . '".');
        }
        return new Directory($this->getFileSystem(), $this->getFullPath($path));
    }

    /**
     * Create a subdirectory into current directory
     *
     * @param $path
     * @return Directory
     * @throws \common_Exception
     */
    public function addDirectory($path)
    {
        if (! $this->getFileSystem()->createDir($this->getFullPath($path))) {
            throw new \common_Exception('An error has occured during directory creation '
                . '(' . $this->getFullPath($path). ').');
        }
        return new Directory($this->getFileSystem(), $this->getFullPath($path));
    }

    /**
     * Remove a subdirectory from current directory
     *
     * @param $path
     * @return Directory
     * @throws \common_Exception
     */
    public function removeDirectory($path)
    {
        if (! $this->getFileSystem()->deleteDir($this->getFullPath($path))) {
            throw new \common_Exception('An error has occured during directory deletion '
                . '(' . $this->getFullPath($path). ').');
        }
        return true;
    }

    /**
     * Delete the current directory
     * @return bool
     */
    public function remove()
    {
        return $this->getFileSystem()->deleteDir($this->path);
    }

    /**
     *  If found return the file at $path location
     *
     * @param $path
     * @return File
     * @throws \tao_models_classes_FileNotFoundException
     */
    public function getFile($path)
    {
        if (! $this->hasFile($path)) {
            throw new \tao_models_classes_FileNotFoundException('Directory "' . $path . '" not found '
                . 'into directory "' . $this->getRelativePath() . '".');
        }
        return $this->spawnFile($path);
    }

    /**
     * Create a file object representing a file (existing or not)
     * @param $path
     * @return File
     */
    public function spawnFile($path)
    {
        return new File($this->getFileSystem(), $this->getFullPath($path));
    }
}