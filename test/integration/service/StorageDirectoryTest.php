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
 */

namespace oat\tao\test\integration\service;

use common_Exception;
use League\Flysystem\Adapter\AbstractAdapter;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;
use oat\generis\test\TestCase;
use tao_models_classes_service_StorageDirectory;

class StorageDirectoryTest extends TestCase
{
    protected $fileSystemTmpId;
    protected $fileSystem;
    protected $idFixture;
    protected $pathFixture;
    protected $accessProvider;

    /** @var  tao_models_classes_service_StorageDirectory */
    protected $instance;

    public function setUp(): void
    {
        $this->fileSystemTmpId = 'test_' . uniqid();
        $fileSystemService = ServiceManager::getServiceManager()->get(FileSystemService::SERVICE_ID);
        $this->fileSystem = $fileSystemService->createLocalFileSystem($this->fileSystemTmpId);

        $this->idFixture = 123;
        $this->pathFixture = 'fixture';
        $this->accessProvider = $this->getAccessProvider($this->pathFixture);

        $this->instance = new tao_models_classes_service_StorageDirectory(
            $this->idFixture,
            $this->fileSystemTmpId,
            $this->pathFixture,
            $this->accessProvider
        );
        $this->instance->setServiceLocator(ServiceManager::getServiceManager());
    }

    public function tearDown(): void
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
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->rrmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }

    protected function getAccessProvider($pathFixture)
    {
        $providerFixture = $this->getMockBuilder('oat\tao\model\websource\TokenWebSource')->getMock();
        $providerFixture->method('getAccessUrl')->with($pathFixture . DIRECTORY_SEPARATOR)->willReturn('polop');

        return $providerFixture;
    }

    public function testAssertInstanceOfDirectory()
    {
        $this->assertInstanceOf(Directory::class, $this->instance);
        $this->assertEquals($this->idFixture, $this->instance->getId());
        $this->assertEquals($this->pathFixture, $this->instance->getPrefix());
        $this->assertEquals($this->fileSystem, $this->instance->getFileSystem());
    }

    public function testIsPublic()
    {
        $this->assertTrue($this->instance->isPublic());
        $this->assertEquals('polop', $this->instance->getPublicAccessUrl());
    }

    public function testIsNotPublic()
    {
        $this->instance = new tao_models_classes_service_StorageDirectory(
            $this->idFixture,
            $this->fileSystem,
            $this->pathFixture,
            null
        );
        $this->instance->setServiceLocator(ServiceManager::getServiceManager());

        $this->assertFalse($this->instance->isPublic());

        $this->expectException(common_Exception::class);
        $this->instance->getPublicAccessUrl();
    }

    /**
     * Test read and write from resource
     */
    public function testReadWrite()
    {
        $tmpFile = uniqid() . '.php';
        $content = file_get_contents(__DIR__ . '/samples/43bytes.php', 'r');

        $file = $this->instance->getFile($tmpFile);
        $file->write($content);

        $this->assertEquals($content, $file->read());
    }

    /**
     * Test read and write from stream
     */
    public function testWriteReadPHPStream()
    {
        $tmpFile = uniqid() . '.php';
        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');

        $file = $this->instance->getFile($tmpFile);
        $file->write($resource);
        fclose($resource);

        $readFixture = $file->readStream();
        $this->assertEquals(
            file_get_contents(__DIR__ . '/samples/43bytes.php'),
            stream_get_contents($readFixture)
        );
        fclose($readFixture);
    }

    public function testWriteReadPsrStream()
    {
        $tmpFile = uniqid() . '.php';

        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        $streamFixture = \GuzzleHttp\Psr7\Utils::streamFor($resource);

        $file = $this->instance->getFile($tmpFile);
        $file->write($streamFixture);
        fclose($resource);
        $streamFixture->close();

        $readStreamFixture = $file->readPsrStream();
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Stream::class, $readStreamFixture);
        $this->assertEquals(
            file_get_contents(__DIR__ . '/samples/43bytes.php'),
            $readStreamFixture->getContents()
        );
        $readStreamFixture->close();
    }

    /**
     * Test write stream in case of remote resource
     */
    public function testWriteRemoteStream()
    {
        $tmpFile = uniqid() . '.php';

        $client = new \GuzzleHttp\Client();
        $response = $client->get('http://www.google.org');
        $this->assertTrue($this->instance->getFile($tmpFile)->write($response->getBody()));
        $this->assertNotEquals(0, $response->getBody()->getSize());
    }
    /**
     * Test write stream in case of remote resource
     */
    public function testSeekToEndOfFileForWriteStream()
    {
        $tmpFile = uniqid() . '.php';

        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        fseek($resource, 43);
        $streamFixture = \GuzzleHttp\Psr7\Utils::streamFor($resource);

        $file = $this->instance->getFile($tmpFile);
        $file->write($streamFixture);
        $streamFixture->rewind();
        $this->assertEquals($streamFixture->getContents(), $file->readPsrStream()->getContents());
        fclose($resource);
        $streamFixture->close();
    }

    /**
     * Test exception for unseekable resource
     */
    public function testUnseekableResource()
    {
        $tmpFile = uniqid() . '.php';

        // @todo requesting example.com is not a good idea as test should ideally be isolated from external world
        $resource = fopen('http://example.com', 'r');
        $streamFixture = \GuzzleHttp\Psr7\Utils::streamFor($resource);
        $this->expectException(common_Exception::class);
        $this->instance->getFile($tmpFile)->write($streamFixture);
        fclose($resource);
        $streamFixture->close();
    }

    /**
     * Test has and delete file function with no file
     */
    public function testHasAndDeleteWithNoFile()
    {
        $tmpFile = uniqid() . '.php';
        $file = $this->instance->getFile($tmpFile);
        $this->assertFalse($file->exists());
        $this->assertFalse($file->delete());
    }

    /**
     * Test has and delete file function with valid file
     */
    public function testHasAndDeleteWithValidFile()
    {
        $tmpFile = uniqid() . '.php';

        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        $streamFixture = \GuzzleHttp\Psr7\Utils::streamFor($resource);
        $file = $this->instance->getFile($tmpFile);
        $file->write($streamFixture);

        $this->assertTrue($file->exists());
        $this->assertTrue($file->delete());

        fclose($resource);
        $streamFixture->close();
    }

    public function testGetPath()
    {
        $reflectionClass = new \ReflectionClass(AbstractAdapter::class);
        $reflectionProperty = $reflectionClass->getProperty('pathPrefix');
        $reflectionProperty->setAccessible(true);

        $path = $reflectionProperty->getValue($this->fileSystem->getAdapter()) . $this->pathFixture;
        $this->assertEquals($path, $this->instance->getPath());
    }
}
