<?php

namespace oat\tao\model\service;

class File extends \League\Flysystem\File
{
    public function getRelativePath()
    {
        return $this->getPath();
    }

    public function getBasename()
    {
        return basename($this->path);
    }

    public function getDirname()
    {
        return dirname($this->path);
    }

    public function getFileInfo()
    {
        return array(
            'name'     => $this->getBasename(),
            'uri'      => $this->getPath(),
            'mime'     => $this->getMimetype(),
            'filePath' => $this->getDirname(),
            'size'     => $this->getSize(),
        );
    }
}