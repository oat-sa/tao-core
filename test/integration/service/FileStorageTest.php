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
 */

namespace oat\tao\test\integration\service;

use oat\tao\test\integration\FileStorageTestCase;

class FileStorageTest extends FileStorageTestCase
{
    /**
     * Test if delete directory works
     */
    public function testDeleteDirectoryById()
    {
        $id = 'polop-';
        $file = 'test.txt';
        
        $this->assertFalse(\tao_helpers_File::containsFileType($this->privateDir, 'txt', true));

        $fileStorage = $this->getFileStorage();

        $directoryStorage = $fileStorage->getDirectoryById($id);
        $stream = fopen('data://text/plain;base64,' . base64_encode('testContent'),'r');
        $directoryStorage->writeStream($file, $stream);

        $this->assertTrue($directoryStorage->has($file));
        $this->assertTrue(\tao_helpers_File::containsFileType($this->privateDir, 'txt', true));
        
        $this->assertTrue($fileStorage->deleteDirectoryById($id));
        $this->assertFalse($directoryStorage->has($file));
        $this->assertFalse(\tao_helpers_File::containsFileType($this->privateDir, 'txt', true));
        
        $reflectionClass = new \ReflectionClass('\tao_models_classes_service_FileStorage');
        $reflectionMethod = $reflectionClass->getMethod('id2path');
        $reflectionMethod->setAccessible(true);
        $path = $reflectionMethod->invokeArgs($fileStorage, [$id]);

        // check for the directory itself
        $this->assertFalse(file_exists($path));
    }
}