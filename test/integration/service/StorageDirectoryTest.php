<?php

declare(strict_types=1);

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

use League\Flysystem\Adapter\AbstractAdapter;
use oat\generis\test\TestCase;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;

class StorageDirectoryTest extends TestCase
{
    protected $fileSystemTmpId;

    protected $fileSystem;

    protected $idFixture;

    protected $pathFixture;

    protected $accessProvider;

    /** @var \tao_models_classes_service_StorageDirectory */
    protected $instance;

    protected function setUp(): void
    {
        $this->fileSystemTmpId = 'test_' . uniqid();
        $fileSystemService = ServiceManager::getServiceManager()->get(FileSystemService::SERVICE_ID);
        $this->fileSystem = $fileSystemService->createLocalFileSystem($this->fileSystemTmpId);

        $this->idFixture = 123;
        $this->pathFixture = 'fixture';
        $this->accessProvider = $this->getAccessProvider($this->pathFixture);

        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystemTmpId, $this->pathFixture, $this->accessProvider);
        $this->instance->setServiceLocator(ServiceManager::getServiceManager());
    }

    protected function tearDown(): void
    {
        $fileSystemService = ServiceManager::getServiceManager()->get(FileSystemService::SERVICE_ID);
        $fileSystemService->unregisterFileSystem($this->fileSystemTmpId);

        $reflectionClass = new \ReflectionClass(AbstractAdapter::class);
        $reflectionProperty = $reflectionClass->getProperty('pathPrefix');
        $reflectionProperty->setAccessible(true);

        $this->rrmdir($reflectionProperty->getValue($this->fileSystem->getAdapter()));

        $this->instance = false;
    }

    public function testAssertInstanceOfDirectory(): void
    {
        $this->assertInstanceOf(Directory::class, $this->instance);
        $this->assertSame($this->idFixture, $this->instance->getId());
        $this->assertSame($this->pathFixture, $this->instance->getPrefix());
        $this->assertSame($this->fileSystem, $this->instance->getFileSystem());
    }

    public function testIsPublic(): void
    {
        $this->assertTrue($this->instance->isPublic());
        $this->assertSame('polop', $this->instance->getPublicAccessUrl());
    }

    public function testIsNotPublic(): void
    {
        $this->instance = new \tao_models_classes_service_StorageDirectory($this->idFixture, $this->fileSystem, $this->pathFixture, null);
        $this->instance->setServiceLocator(ServiceManager::getServiceManager());

        $this->assertFalse($this->instance->isPublic());

        $this->setExpectedException(\common_Exception::class);
        $this->instance->getPublicAccessUrl();
    }

    /**
     * Test read and write from resource
     */
    public function testReadWrite(): void
    {
        $tmpFile = uniqid() . '.php';
        $content = file_get_contents(__DIR__ . '/samples/43bytes.php', 'r');

        $file = $this->instance->getFile($tmpFile);
        $file->write($content);

        $this->assertSame($content, $file->read());
    }

    /**
     * Test read and write from stream
     */
    public function testWriteReadPHPStream(): void
    {
        $tmpFile = uniqid() . '.php';
        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');

        $file = $this->instance->getFile($tmpFile);
        $file->write($resource);
        fclose($resource);

        $readFixture = $file->readStream();
        $this->assertSame(
            file_get_contents(__DIR__ . '/samples/43bytes.php'),
            stream_get_contents($readFixture)
        );
        fclose($readFixture);
    }

    public function testWriteReadPsrStream(): void
    {
        $tmpFile = uniqid() . '.php';

        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        $streamFixture = \GuzzleHttp\Psr7\stream_for($resource);

        $file = $this->instance->getFile($tmpFile);
        $file->write($streamFixture);
        fclose($resource);
        $streamFixture->close();

        $readStreamFixture = $file->readPsrStream();
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Stream::class, $readStreamFixture);
        $this->assertSame(
            file_get_contents(__DIR__ . '/samples/43bytes.php'),
            $readStreamFixture->getContents()
        );
        $readStreamFixture->close();
    }

    /**
     * Test write stream in case of remote resource
     */
    public function testWriteRemoteStream(): void
    {
        $tmpFile = uniqid() . '.php';

        $client = new \GuzzleHttp\Client();
        $response = $client->get('http://www.google.org');
        $this->assertTrue($this->instance->getFile($tmpFile)->write($response->getBody()));
        $this->assertNotSame(0, $response->getBody()->getSize());
    }

    /**
     * Test write stream in case of remote resource
     */
    public function testSeekToEndOfFileForWriteStream(): void
    {
        $tmpFile = uniqid() . '.php';

        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        fseek($resource, 43);
        $streamFixture = \GuzzleHttp\Psr7\stream_for($resource);

        $file = $this->instance->getFile($tmpFile);
        $file->write($streamFixture);
        $streamFixture->rewind();
        $this->assertSame($streamFixture->getContents(), $file->readPsrStream()->getContents());
        fclose($resource);
        $streamFixture->close();
    }

    /**
     * Test exception for unseekable resource
     */
    public function testUnseekableResource(): void
    {
        $tmpFile = uniqid() . '.php';

        // @todo requesting example.com is not a good idea as test should ideally be isolated from external world
        $resource = fopen('http://example.com', 'r');
        $streamFixture = \GuzzleHttp\Psr7\stream_for($resource);
        $this->setExpectedException(\common_Exception::class);
        $this->instance->getFile($tmpFile)->write($streamFixture);
        fclose($resource);
        $streamFixture->close();
    }

    /**
     * Test has and delete file function with no file
     */
    public function testHasAndDeleteWithNoFile(): void
    {
        $tmpFile = uniqid() . '.php';
        $file = $this->instance->getFile($tmpFile);
        $this->assertFalse($file->exists());
        $this->assertFalse($file->delete());
    }

    /**
     * Test has and delete file function with valid file
     */
    public function testHasAndDeleteWithValidFile(): void
    {
        $tmpFile = uniqid() . '.php';

        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        $streamFixture = \GuzzleHttp\Psr7\stream_for($resource);
        $file = $this->instance->getFile($tmpFile);
        $file->write($streamFixture);

        $this->assertTrue($file->exists());
        $this->assertTrue($file->delete());

        fclose($resource);
        $streamFixture->close();
    }

    public function testGetPath(): void
    {
        $reflectionClass = new \ReflectionClass(AbstractAdapter::class);
        $reflectionProperty = $reflectionClass->getProperty('pathPrefix');
        $reflectionProperty->setAccessible(true);

        $path = $reflectionProperty->getValue($this->fileSystem->getAdapter()) . $this->pathFixture;
        $this->assertSame($path, $this->instance->getPath());
    }

    protected function rrmdir($dir): void
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
}
