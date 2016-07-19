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
use oat\tao\test\TaoPhpUnitTestRunner;
use Zend\ServiceManager\ServiceLocatorInterface;

class FileStorageTest extends TaoPhpUnitTestRunner
{
    protected $sampleDir;
    protected $publicDir;
    protected $privateDir;
    protected $adapterFixture;

    /**
     * tests initialization
     */
    public function setUp()
    {
        $this->sampleDir = __DIR__ . '/samples/';
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
     * Create a mock of filesystem, getUri will return $path
     * @param $path
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFileSystemMock($path)
    {
        $fsFixture = $this->getMockBuilder('core_kernel_fileSystem_FileSystem')
            ->disableOriginalConstructor()
            ->getMock();
        $fsFixture->method('getUri')->willReturn($path);

        return $fsFixture;
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
            'filesPath' => $this->sampleDir,
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
        $fileStorage = \tao_models_classes_service_FileStorage::singleton();
        $reflectionClass = new \ReflectionClass('\tao_models_classes_service_FileStorage');

        $reflectionProperty = $reflectionClass->getProperty('privateFs');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($fileStorage, $this->getFileSystemMock($this->adapterFixture));

        $fileStorage->setServiceLocator($this->getServiceLocatorWithFileSystem());

        return $fileStorage;
    }

    /**
     * Test if delete directory works
     */
    public function testDeleteDirectoryById()
    {
        $id = 'polop-';
        $file = 'test';

        $fileStorage = $this->getFileStorage();

        $directoryStorage = $fileStorage->getDirectoryById($id);
        $stream = fopen('data://text/plain;base64,' . base64_encode('testContent'),'r');
        $directoryStorage->write($file, $stream);

        $this->assertTrue($fileStorage->deleteDirectoryById($id));
        $this->assertFalse($directoryStorage->has($file));

        $reflectionClass = new \ReflectionClass('\tao_models_classes_service_FileStorage');
        $reflectionMethod = $reflectionClass->getMethod('id2path');
        $reflectionMethod->setAccessible(true);
        $path = $reflectionMethod->invokeArgs($fileStorage, [$id]);

        $this->assertFalse(file_exists($path));
    }
}