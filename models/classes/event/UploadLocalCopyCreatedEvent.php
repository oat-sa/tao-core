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
namespace oat\tao\model\event;


use oat\oatbox\event\Event;
use oat\oatbox\filesystem\File;

/**
 * The purpose is to track association between FlyFiles and legacy one, while use to cleanup FlySystem storage
 * Class UploadLocalCopyCreatedEvent
 * @package oat\tao\model\event
 */
class UploadLocalCopyCreatedEvent implements Event
{
    private $file;
    private $tmpPath;

    /**
     * UploadLocalCopyCreatedEvent constructor.
     * @param File $file
     * @param $tmpPath
     */
    public function __construct(File $file, $tmpPath)
    {
        \common_Logger::d('TMP - file' . $file->getPrefix() . 'has been copied to ' . $tmpPath);
        $this->file = $file;
        $this->tmpPath = $tmpPath;
    }


    public function getName()
    {
        return __CLASS__;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return mixed
     */
    public function getTmpPath()
    {
        return $this->tmpPath;
    }


}