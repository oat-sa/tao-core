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
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;
use oat\tao\test\TaoPhpUnitTestRunner;

class StorageDirectoryTest extends TaoPhpUnitTestRunner
{
    protected $fileSystemTmpId;
    protected $fileSystem;
    protected $idFixture;
    protected $pathFixture;
    protected $accessProvider;

    /** @var  \tao_models_classes_service_StorageDirectory */
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

        $this->rrmdir($reflectionProperty->getValue($this->fileSystem->getAdapter()));

        $this->instance = false;
    }

    protected function rrmdir($dir)
    {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file)) {
                $this->rrmdir($file);
            }
            else {
                unlink($file);
            }
        }
        rmdir($dir);
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

    /**
     * Test read and write from resource
     */
    public function testReadWrite()
    {
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, $this->accessProvider);

        $tmpFile = uniqid() . '.php';
        $content = file_get_contents(__DIR__ . '/samples/43bytes.php', 'r');
        $this->instance->write($tmpFile, $content);

        $readFixture = $this->instance->read($tmpFile);

        $this->assertEquals($content, $readFixture);
    }

    /**
     * Test read and write from stream
     */
    public function testWriteReadPHPStream()
    {
        $tmpFile = uniqid() . '.php';
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, $this->accessProvider);

        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        $this->instance->writeStream($tmpFile, $resource);
        fclose($resource);

        $readFixture = $this->instance->readStream($tmpFile);

        $this->assertEquals(
            file_get_contents(__DIR__ . '/samples/43bytes.php'),
            stream_get_contents($readFixture)
        );
        fclose($readFixture);
    }

    public function testWriteReadPsrStream()
    {
        $tmpFile = uniqid() . '.php';
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, $this->accessProvider);

        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        $streamFixture = \GuzzleHttp\Psr7\stream_for($resource);
        $this->instance->writePsrStream($tmpFile, $streamFixture);
        fclose($resource);
        $streamFixture->close();

        $readStreamFixture = $this->instance->readPsrStream($tmpFile);
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Stream::class, $readStreamFixture);
        $this->assertEquals(
            file_get_contents(__DIR__ . '/samples/43bytes.php'),
            $readStreamFixture->getContents()
        );
        $readStreamFixture->close();
    }
    /**
    /**
     * Test write stream in case of remote resource
     */
    public function testWriteRemoteStream()
    {
        $tmpFile = uniqid() . '.php';
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, $this->accessProvider);
        $client = new \GuzzleHttp\Client();
        $response = $client->get('http://www.google.org');
        $this->assertTrue($this->instance->writeStream($tmpFile, $response->getBody()));
        $this->assertNotEquals(0, $response->getBody()->getSize());
    }
    /**
     * Test write stream in case of remote resource
     */
    public function testSeekToEndOfFileForWriteStream()
    {
        $tmpFile = uniqid() . '.php';
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, $this->accessProvider);
        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        fseek($resource, 43);
        $streamFixture = \GuzzleHttp\Psr7\stream_for($resource);
        $this->instance->writePsrStream($tmpFile, $streamFixture);
        $streamFixture->rewind();
        $this->assertEquals($streamFixture->getContents(), $this->instance->readPsrStream($tmpFile)->getContents());
        fclose($resource);
        $streamFixture->close();
    }

    /**
     * Test exception for unseekable resource
     */
    public function testUnseekableResource()
    {
        $tmpFile = uniqid() . '.php';
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, $this->accessProvider);
        $resource = fopen('http://www.google.org', 'r');
        $streamFixture = \GuzzleHttp\Psr7\stream_for($resource);
        $this->setExpectedException(\common_Exception::class);
        $this->instance->writeStream($tmpFile, $streamFixture);
        fclose($resource);
        $streamFixture->close();
    }

    /**
     * Test has and delete file function with no file
     */
    public function testHasAndDeleteWithNoFile()
    {
        $tmpFile = uniqid() . '.php';
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, $this->accessProvider);
        $this->assertFalse($this->instance->has($tmpFile));
        $this->assertFalse($this->instance->delete($tmpFile));
    }

    /**
     * Test has and delete file function with valid file
     */
    public function testHasAndDeleteWithValidFile()
    {
        $tmpFile = uniqid() . '.php';
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, $this->accessProvider);
        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        $streamFixture = \GuzzleHttp\Psr7\stream_for($resource);
        $this->instance->writePsrStream($tmpFile, $streamFixture);
        $this->assertTrue($this->instance->has($tmpFile));
        $this->assertTrue($this->instance->delete($tmpFile));
        fclose($resource);
        $streamFixture->close();
    }
}