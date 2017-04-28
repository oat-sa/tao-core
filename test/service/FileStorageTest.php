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

namespace oat\tao\test\service;

use oat\oatbox\filesystem\FileSystemService;
use Zend\ServiceManager\ServiceLocatorInterface;

class FileStorageTest extends \PHPUnit_Framework_TestCase
{
    protected $privateDir;
    protected $adapterFixture;

    /**
     * tests initialization
     */
    public function setUp()
    {
        $this->privateDir = \tao_helpers_File::createTempDir();
        $this->adapterFixture = 'adapterFixture';
    }

    /**
     * Remove directory of $adapterFixture
     */
    public function tearDown()
    {
        \tao_helpers_File::delTree($this->privateDir);
    }

    /**
     * Create serviceLocator with custom filesystem using adapter for sample
     * Two adapters needed to reflect private/public dir
     *
     * @return object
     */
    protected function getServiceLocatorWithFileSystem()
    {
        $adaptersFixture = array (
            'adapters' => array (
                $this->adapterFixture => array(
                    'class' => 'Local',
                    'options' => array(
                        'root' => $this->privateDir
                    )
                )
            )
        );

        $fileSystemService = new FileSystemService($adaptersFixture);

        $smProphecy = $this->prophesize(ServiceLocatorInterface::class);
        $smProphecy->get(FileSystemService::SERVICE_ID)->willReturn($fileSystemService);
        return $smProphecy->reveal();
    }

    /**
     * Get file storage to test
     * Set service locator to have fileSystem with test adapters
     * Set publicFs & privateFs to match with adapters
     *
     * @return \tao_models_classes_service_FileStorage
     */
    public function getFileStorage()
    {
        $fileStorage = new \tao_models_classes_service_FileStorage([
            \tao_models_classes_service_FileStorage::OPTION_PRIVATE_FS => $this->adapterFixture
        ]);
        $fileStorage->setServiceLocator($this->getServiceLocatorWithFileSystem());

        return $fileStorage;
    }

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