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
use oat\tao\model\service\Directory;
use oat\tao\model\service\File;
use oat\tao\test\TaoPhpUnitTestRunner;
use oat\oatbox\service\ServiceManager;

class DirectoryTest extends TaoPhpUnitTestRunner
{
    protected $fileSystemTmpId;
    protected $fileSystem;

    /** @var Directory */
    protected $instance;

    public function setUp()
    {
        $this->fileSystemTmpId = 'test_' . uniqid();
        $fileSystemService = ServiceManager::getServiceManager()->get(FileSystemService::SERVICE_ID);
        $this->fileSystem = $fileSystemService->createLocalFileSystem($this->fileSystemTmpId);

        $pathFixture = 'fixture';

        $this->instance = new Directory($this->fileSystem, $pathFixture);
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

    public function testConstruct()
    {
        $this->instance = new Directory($this->fileSystem);

        $reflectionClass = new \ReflectionClass(Directory::class);
        $reflectionProperty = $reflectionClass->getProperty('path');
        $reflectionProperty->setAccessible(true);
        $this->assertEquals('.', $reflectionProperty->getValue($this->instance));

        $this->assertTrue($this->instance->isDir());
        $this->assertFalse($this->instance->isFile());
    }

    public function testGetFileSystem()
    {
        $reflectionClass = new \ReflectionClass(Directory::class);
        $reflectionMethod = $reflectionClass->getMethod('getFileSystem');
        $reflectionMethod->setAccessible(true);
        $this->assertEquals($this->fileSystem, $reflectionMethod->invoke($this->instance));
    }

    /**
     * @dataProvider sanitizePathProvider
     */
    public function testSanitizePath($path ,$expected)
    {
        $reflectionClass = new \ReflectionClass(Directory::class);
        $reflectionMethod = $reflectionClass->getMethod('sanitizePath');
        $reflectionMethod->setAccessible(true);
        $this->assertEquals($expected, $reflectionMethod->invoke($this->instance, $path));
    }

    /**
     * @dataProvider sanitizePathProvider
     */
    public function testGetRelativePath($path ,$expected)
    {
        $reflectionClass = new \ReflectionClass(Directory::class);
        $reflectionProperty = $reflectionClass->getProperty('path');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->instance, $path);

        $this->assertEquals($expected, $this->instance->getRelativePath());
    }

    public function sanitizePathProvider()
    {
        return [
            ['polop/polop', 'polop/polop'],
            ['polop\polop', 'polop/polop'],
            ['polop\polop/polop', 'polop/polop/polop'],
            ['polop', 'polop'],
        ];
    }

    /**
     * @dataProvider getFullPathProvider
     */
    public function testGetFullPath($relativePath, $path, $expected)
    {
        $reflectionClass = new \ReflectionClass(Directory::class);
        $reflectionProperty = $reflectionClass->getProperty('path');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->instance, $relativePath);

        $reflectionClass = new \ReflectionClass(Directory::class);
        $reflectionMethod = $reflectionClass->getMethod('getFullPath');
        $reflectionMethod->setAccessible(true);
        $this->assertEquals($expected, $reflectionMethod->invoke($this->instance, $path));
    }

    public function getFullPathProvider()
    {
        return [
            ['polop', 'polop', 'polop/polop'],
            ['polop/', '/polop', 'polop/polop'],
            ['polop\\', '\polop', 'polop/polop'],
        ];
    }

    /**
     * @dataProvider writeReadUpdateProvider
     */
    public function testWriteReadUpdate($path, $content, $exception, $expected)
    {
        if ($exception) {
            $this->setExpectedException($exception);
        }
        $this->assertEquals($expected, $this->instance->write($path, $content));

        if (! $exception) {
            $this->assertEquals($content, $this->instance->read($path));
            $this->assertTrue($this->instance->update($path, 'newContent'));
            $this->assertEquals('newContent', $this->instance->read($path));
        }
    }

    public function writeReadUpdateProvider()
    {
        return [
            ['polop', 'polop', null, true],
            ['polop', 'polop', null, true],
            ['polop', null, \InvalidArgumentException::class, false],
            ['polop/polop', 'polop', null, true],
        ];
    }

    public function testWriteUpdateReadPsrStream()
    {
        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        $content = file_get_contents(__DIR__ . '/samples/43bytes.php', 'r');
        fseek($resource, 43);
        $streamFixture = \GuzzleHttp\Psr7\stream_for($resource);

        $this->assertTrue($this->instance->writePsrStream('polop', $streamFixture));
        $this->assertEquals($content, $this->instance->readPsrStream('polop')->getContents());

        $this->assertTrue($this->instance->update('polop', 'otherContent'));
        $this->assertEquals('otherContent', $this->instance->readPsrStream('polop')->getContents());

        $this->assertTrue($this->instance->updatePsrStream('polop', $streamFixture));
        $this->assertEquals($content, $this->instance->readPsrStream('polop')->getContents());
    }

    public function testNotSeekableStreamForWritePsrStream()
    {
        $resource = fopen('http://www.google.org', 'r');
        $streamFixture = \GuzzleHttp\Psr7\stream_for($resource);

        $this->setExpectedException(\common_Exception::class);
        $this->instance->writePsrStream('polop', $streamFixture);
    }

    public function testNotSeekableStreamForUpdatePsrStream()
    {
        $resource = fopen('http://www.google.org', 'r');
        $streamFixture = \GuzzleHttp\Psr7\stream_for($resource);

        $this->setExpectedException(\common_Exception::class);
        $this->instance->updatePsrStream('polop', $streamFixture);
    }

    public function testNotSeekableStreamForUpdatePHPStream()
    {
        $resource = fopen('http://www.google.org', 'r');

        $this->setExpectedException(\common_Exception::class);
        $this->instance->updateStream('polop', $resource);
    }

    public function testWriteUpdateReadPHPStream()
    {
        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        $content = file_get_contents(__DIR__ . '/samples/43bytes.php', 'r');
        fseek($resource, 43);

        $this->assertTrue($this->instance->writeStream('polop', $resource));
        $this->assertEquals($content, stream_get_contents($this->instance->readStream('polop')));

        $this->assertTrue($this->instance->update('polop', 'otherContent'));
        $this->assertEquals('otherContent', stream_get_contents($this->instance->readStream('polop')));

        $this->assertTrue($this->instance->updateStream('polop', $resource));
        $this->assertEquals($content, stream_get_contents($this->instance->readStream('polop')));

        fclose($resource);
    }

    public function testHasMethodsIntoDirectories()
    {
        $this->assertFalse($this->instance->has('polop'));
        $this->assertFalse($this->instance->hasFile('polop'));
        $this->assertFalse($this->instance->hasDirectory('polop'));

        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        $this->assertTrue($this->instance->writeStream('polop', $resource));
        fclose($resource);

        $this->assertTrue($this->instance->has('polop'));
        $this->assertTrue($this->instance->hasFile('polop'));
        $this->assertFalse($this->instance->hasDirectory('polop'));

        $this->assertTrue($this->instance->delete('polop'));
        $subDirecory = $this->instance->addDirectory('polop/polop');
        $this->assertEquals('fixture/polop/polop', $subDirecory->getRelativePath());

        $this->assertTrue($this->instance->has('polop'));
        $this->assertFalse($this->instance->hasFile('polop'));
        $this->assertTrue($this->instance->hasDirectory('polop'));
        $this->assertFalse($subDirecory->has('polop'));

        $this->assertTrue($this->instance->deleteDirectory('polop'));

        $this->assertFalse($this->instance->has('polop'));
        $this->assertFalse($this->instance->hasFile('polop'));
        $this->assertFalse($this->instance->hasDirectory('polop'));
    }

    public function testDirectoryMethod()
    {
        $directoryFixture = new Directory($this->fileSystem, 'fixture/polop/polop');

        $this->assertFalse($this->instance->hasDirectory('polop'));

        $this->assertEquals($directoryFixture, $this->instance->addDirectory('polop/polop'));
        $this->assertTrue($this->instance->hasDirectory('polop'));
        $this->assertTrue($this->instance->hasDirectory('polop/polop'));

        $subDirectoryFixture = new Directory($this->fileSystem, 'fixture/polop');
        $this->assertEquals($subDirectoryFixture, $this->instance->getDirectory('polop'));

        $this->assertTrue($this->instance->deleteDirectory('polop/polop'));
        $this->assertTrue($this->instance->hasDirectory('polop'));
        $this->assertFalse($this->instance->hasDirectory('polop/polop'));

        $this->assertEquals($directoryFixture, $this->instance->addDirectory('polop/polop'));
        $this->assertTrue($this->instance->deleteDirectory('polop'));
        $this->assertFalse($this->instance->hasDirectory('polop'));
        $this->assertFalse($this->instance->hasDirectory('polop/polop'));

        $this->setExpectedException(\tao_models_classes_FileNotFoundException::class);
        $this->instance->deleteDirectory('notExists');

    }

    public function testGetSpawnFile()
    {
        $pathFixture = 'polop.txt';
        $this->assertFalse($this->instance->hasFile($pathFixture));

        $file = new File($this->fileSystem, 'fixture/' . $pathFixture);
        $this->assertEquals($file, $this->instance->spawnFile($pathFixture));

        $resource = fopen(__DIR__ . '/samples/43bytes.php', 'r');
        $this->assertTrue($this->instance->writeStream($pathFixture, $resource));
        fclose($resource);

        $this->assertEquals($file, $this->instance->getFile($pathFixture));

        $this->assertTrue($this->instance->delete($pathFixture));
        $this->assertFalse($this->instance->hasFile($pathFixture));

        $this->setExpectedException(\tao_models_classes_FileNotFoundException::class);
        $this->instance->getFile('notExists');
    }

    public function testWriteRemoteStream()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->get('http://www.google.org');
        $this->assertTrue($this->instance->writePsrStream('polop.txt', $response->getBody()));
        $this->assertTrue($this->instance->hasFile('polop.txt'));
        $this->assertNotNull($this->instance->read('polop.txt'));
    }

    public function testIteratorMethods()
    {
        $this->instance->addDirectory('polop/polop/polop/');
        $this->instance->addDirectory('polop/fixture.text');
        $this->instance->addDirectory('other/polop/fixture');
        $this->instance->addDirectory('other/fixture');

        $expected = [
            'polop',
            'polop/polop',
            'polop/polop/polop',
            'polop/fixture.text',
            'other',
            'other/polop',
            'other/polop/fixture',
            'other/fixture'
        ];
        $this->assertEquals(krsort($expected), krsort($this->instance->getDirectoryIterator()->getArrayCopy()));

        $expected = [
            'polop',
            'polop/polop',
            'polop/polop/polop',
            'polop/fixture.text'
        ];
        $this->assertEquals(krsort($expected), krsort($this->instance->getDirectoryIterator('polop')->getArrayCopy())) ;


        $this->instance->spawnFile('abc.xyz')->write('content');
        $this->instance->spawnFile('polop/polop/test')->write('content');
        $this->instance->spawnFile('\other\polop\plop.txt')->write('content');
        $this->instance->spawnFile('other/polop/pilop/')->write('content');
        $this->instance->spawnFile('test.txt')->write('content');
        $subDirectory = $this->instance->getDirectory('other');
        $subDirectory->write('test', 'testContent');

        $expected = [
            "other/abc.xyz",
            "other/polop/pilop",
            "other/polop/plop.txt",
            "other/test",
            "other/polop/polop/test",
            "other/test.txt"
        ];

        $this->assertEquals(krsort($expected), krsort($this->instance->getIterator()->getArrayCopy()));

        $expected = [
            'fixture/other/polop/pilop',
            'fixture/other/polop/plop.txt',
            'fixture/other/test'
        ];

        $this->assertEquals($expected, $subDirectory->getIteratorWithRelativePath()->getArrayCopy());

        $this->setExpectedException(\tao_models_classes_FileNotFoundException::class);
        $this->instance->getDirectoryIterator('polopfixture');
    }
}

