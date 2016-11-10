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

use oat\tao\test\TaoPhpUnitTestRunner;
use oat\tao\model\extension\UpdateLogger;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;

/**
 * @package tao
 */
class UpdateLoggerTest extends TaoPhpUnitTestRunner
{
    public function testLog()
    {
        $tmpDir = $this->getTempDirectory();
        $logger = new UpdateLogger(array(UpdateLogger::OPTION_FILESYSTEM => $tmpDir->getFileSystemId()));
        $logger->setServiceLocator($tmpDir->getServiceLocator());
        
        $files = array();
        foreach ($tmpDir->getIterator(Directory::ITERATOR_FILE) as $file) {
            $files[] = $file;
        }
        $this->assertEquals(0, count($files));
        $logger->error('SampleError');
        
        $files = array();
        foreach ($tmpDir->getIterator(Directory::ITERATOR_FILE) as $file) {
            $files[] = $file;
        }
        $this->assertEquals(1, count($files));
        $file = reset($files);
        
        $content = $file->read();
        $this->assertFalse(strpos($content, 'WeirdString'));
        $this->assertNotFalse(strpos($content, 'SampleError'));
    }

}