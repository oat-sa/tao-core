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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013- (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\helpers\test;

use oat\tao\test\TaoPhpUnitTestRunner;
use tao_helpers_File;
use ZipArchive;

/**
 * @author Cédric Alfonsi, <taosupport@tudor.lu>
 * @package tao
 */
class FileHelperTest extends TaoPhpUnitTestRunner
{
    /** @var int */
    protected $deep = 3;

    /** @var int */
    protected $fileCount = 5;

    /** @var string */
    private $tmpPath;

    /** @var string */
    private $envName;

    /** @var string */
    private $envPath;

    public function __construct()
    {
        $this->tmpPath = sys_get_temp_dir();
        $this->envName = 'ROOT_DIR';
        $this->envPath = $this->tmpPath . '/' . $this->envName;
    }

    public function setUp(): void
    {
        parent::setUp();
        TaoPhpUnitTestRunner::initTest();
        $this->initEnv($this->tmpPath, $this->envName, $this->deep, $this->fileCount);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        tao_helpers_File::remove($this->envPath, true);
        $this->assertFalse(is_dir($this->envPath));
    }

    private function initEnv($root, $name, $deep, $n)
    {
        $envPath = $root . '/' . $name;
        mkdir($envPath);
        $this->assertTrue(is_dir($envPath));
        for ($i = 0; $i < $n; $i++) {
            $tempnam = tempnam($envPath, '');
            $this->assertTrue(is_file($tempnam));
        }
        if ($deep > 0) {
            $this->initEnv($envPath, 'DIR_' . $deep, $deep - 1, $n);
        }
    }

    public function testScanDir()
    {
        $this->assertCount(
            23,
            tao_helpers_File::scanDir($this->envPath, ['recursive' => true])
        );
        $this->assertCount(
            3,
            tao_helpers_File::scanDir($this->envPath, ['only' => tao_helpers_File::$DIR, 'recursive' => true])
        );
        $this->assertCount(
            20,
            tao_helpers_File::scanDir($this->envPath, ['only' => tao_helpers_File::$FILE, 'recursive' => true])
        );
    }

    public function testTempDir()
    {
        $path1 = tao_helpers_File::createTempDir();
        $path2 = tao_helpers_File::createTempDir();
        $this->assertTrue(is_dir($path1));
        $this->assertTrue(is_dir($path2));
        $this->assertNotEquals($path1, $path2);

        $tempnam1 = tempnam($path1, '');
        $this->assertTrue(is_file($tempnam1));

        $subdir2 = $path2 . DIRECTORY_SEPARATOR . 'testdir';
        $this->assertTrue(mkdir($subdir2));
        $this->assertTrue(is_dir($subdir2));
        $tempnam2 = tempnam($subdir2, '');
        $this->assertTrue(is_file($tempnam2));

        $this->assertTrue(tao_helpers_File::delTree($path1));
        $this->assertFalse(is_dir($path1));
        $this->assertFalse(is_file($tempnam1));

        $this->assertTrue(tao_helpers_File::delTree($path2));
        $this->assertFalse(is_dir($path2));
        $this->assertFalse(is_dir($subdir2));
        $this->assertFalse(is_file($tempnam2));
    }

    public function testIsIdentical()
    {

        $testfolder = tao_helpers_File::createTempDir();
        $this->assertTrue(is_dir($testfolder));

        $zip = new ZipArchive();
        $this->assertTrue(
            $zip->open(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . 'fileHelper.zip')
        );
        $this->assertTrue($zip->extractTo($testfolder));
        $zip->close();

        $reference = $testfolder . 'reference';
        $this->assertTrue(is_dir($reference));
        $testContent = $testfolder . DIRECTORY_SEPARATOR . 'testContent';
        $testEmptyDir = $testfolder . DIRECTORY_SEPARATOR . 'testEmptyDir';
        $testIdent = $testfolder . DIRECTORY_SEPARATOR . 'testIdent';
        $testMissingDir = $testfolder . DIRECTORY_SEPARATOR . 'testMissingDir';
        $testRenamedFile = $testfolder . DIRECTORY_SEPARATOR . 'testRenamedFile';
        $testRenamedEmptyDir = $testfolder . DIRECTORY_SEPARATOR . 'testRenamedEmptyDir';

        $this->assertTrue(tao_helpers_File::isIdentical($reference, $reference));
        $this->assertTrue(tao_helpers_File::isIdentical($reference, $testIdent));
        $this->assertFalse(tao_helpers_File::isIdentical($reference, $testContent));
        $this->assertFalse(tao_helpers_File::isIdentical($reference, $testEmptyDir));
        $this->assertFalse(tao_helpers_File::isIdentical($reference, $testMissingDir));
        $this->assertFalse(tao_helpers_File::isIdentical($reference, $testRenamedFile));
        $this->assertFalse(tao_helpers_File::isIdentical($reference, $testRenamedEmptyDir));

        $this->assertTrue(tao_helpers_File::delTree($testfolder));
        $this->assertFalse(is_dir($testfolder));
    }

    public function testRelPath()
    {
        $testDir = tao_helpers_File::createTempDir();
        $this->assertTrue(mkdir($testDir . 'sub' . DIRECTORY_SEPARATOR));

        $path = $testDir . 'sub';
        $this->assertEquals('sub', \tao_helpers_File::getRelPath($testDir, $path));

        $path = $testDir . 'sub' . DIRECTORY_SEPARATOR;
        $this->assertEquals('..' . DIRECTORY_SEPARATOR, \tao_helpers_File::getRelPath($path, $testDir));

        $this->assertTrue(tao_helpers_File::delTree($testDir));
        $this->assertFalse(is_dir($testDir));
    }

    public function testRenameInZip()
    {
        // Prepare test archive.
        $root = $this->envPath;
        $archivePath = "{$root}/rename.zip";
        $zipArchive = new ZipArchive();
        $zipArchive->open($archivePath, ZipArchive::CREATE);
        $zipArchive->addFromString('path/to/data/text.txt', 'some text');
        $zipArchive->addFromString('path/to/log.log', 'some logs');
        $this->assertEquals(2, tao_helpers_File::renameInZip($zipArchive, 'path/to', 'road/to'));
        $zipArchive->close();

        // Actual test.
        $zipArchive = new ZipArchive();
        $zipArchive->open($archivePath, ZipArchive::CREATE);
        $this->assertEquals('some text', $zipArchive->getFromName('road/to/data/text.txt'));
        $this->assertEquals('some logs', $zipArchive->getFromName('road/to/log.log'));
        $zipArchive->close();
    }

    public function testExcludeFromZip()
    {
        // Prepare test archive.
        $root = $this->envPath;
        $archivePath = "{$root}/exclude.zip";
        $zipArchive = new ZipArchive();
        $zipArchive->open($archivePath, ZipArchive::CREATE);
        $zipArchive->addFromString('path/to/data/text.txt', 'some text');
        $zipArchive->addFromString('path/to/log.log', 'some logs');
        $this->assertEquals(1, tao_helpers_File::excludeFromZip($zipArchive, '/.log$/'));
        $zipArchive->close();

        // Actual test.
        $zipArchive = new ZipArchive();
        $zipArchive->open($archivePath, ZipArchive::CREATE);
        $this->assertFalse($zipArchive->getFromName('path/to/log.log'));
        $this->assertNotFalse($zipArchive->getFromName('path/to/data/text.txt'));
        $zipArchive->close();
    }

    public function testGetAllZipNames()
    {
        // Prepare test archive.
        $root = $this->envPath;
        $archivePath = "{$root}/rename.zip";
        $zipArchive = new ZipArchive();
        $zipArchive->open($archivePath, ZipArchive::CREATE);
        $zipArchive->addFromString('path/to/data/text.txt', 'some text');
        $zipArchive->addFromString('path/to/log.log', 'some logs');

        $this->assertEquals(
            ['path/to/data/text.txt', 'path/to/log.log'],
            tao_helpers_File::getAllZipNames($zipArchive)
        );

        $zipArchive->close();
    }

    public function testExtractArchive()
    {
        $path = realpath('./samples/samples.zip');
        $dir = tao_helpers_File::extractArchive($path);
        $this->assertTrue(is_dir($dir));
        $files = scandir($dir, SCANDIR_SORT_ASCENDING);
        $this->assertCount(4, $files);
        $this->assertContains('bar.json', $files);
        $this->assertContains('foo.txt', $files);
    }
}
