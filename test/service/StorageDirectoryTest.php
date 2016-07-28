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

use League\Flysystem\Adapter\AbstractAdapter;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\service\Directory;
use oat\tao\test\TaoPhpUnitTestRunner;

class StorageDirectoryTest extends TaoPhpUnitTestRunner
{
    protected $fileSystemTmpId;
    protected $fileSystem;
    protected $idFixture;
    protected $pathFixture;
    protected $accessProvider;

    protected $instance;

    public function setUp()
    {
        $this->fileSystemTmpId = 'test_' . uniqid();
        $fileSystemService = ServiceManager::getServiceManager()->get(FileSystemService::SERVICE_ID);
        $this->fileSystem = $fileSystemService->createLocalFileSystem($this->fileSystemTmpId);

        $this->idFixture = 123;
        $this->pathFixture = 'fixture';
        $this->accessProvider = $this->getAccessProvider($this->pathFixture);
    }

    public function tearDown()
    {
        $fileSystemService = ServiceManager::getServiceManager()->get(FileSystemService::SERVICE_ID);
        $fileSystemService->unregisterFileSystem($this->fileSystemTmpId);

        $reflectionClass = new \ReflectionClass(AbstractAdapter::class);
        $reflectionProperty = $reflectionClass->getProperty('pathPrefix');
        $reflectionProperty->setAccessible(true);

        rmdir($reflectionProperty->getValue($this->fileSystem->getAdapter()));

        $this->instance = false;
    }

    protected function getAccessProvider($pathFixture)
    {
        $providerFixture = $this->getMockBuilder('oat\tao\model\websource\TokenWebSource')->getMock();
        $providerFixture->method('getAccessUrl')->with($pathFixture)->willReturn('polop');

        return $providerFixture;
    }

    public function testAssertInstanceOfDirectory()
    {
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, $this->accessProvider);

        $this->assertInstanceOf(Directory::class, $this->instance);
        $this->assertEquals($this->idFixture, $this->instance->getId());
        $this->assertEquals($this->pathFixture, $this->instance->getRelativePath());

        $reflectionClass = new \ReflectionClass(Directory::class);
        $reflectionMethod = $reflectionClass->getMethod('getFileSystem');
        $reflectionMethod->setAccessible(true);
        $this->assertEquals($this->fileSystem, $reflectionMethod->invoke($this->instance));
    }

    public function testIsPublic()
    {
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, $this->accessProvider);
        $this->assertTrue($this->instance->isPublic());
        $this->assertEquals('polop', $this->instance->getPublicAccessUrl());
    }

    public function testIsNotPublic()
    {
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, null);
        $this->assertFalse($this->instance->isPublic());

        $this->setExpectedException(\common_Exception::class);
        $this->instance->getPublicAccessUrl();
    }
}