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
 *               2012-2016 Open Assessment Technologies SA
 *
 */

namespace oat\tao\test;

use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;
use PHPUnit\Framework\MockObject\MockObject;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use oat\generis\test\KeyValueMockTrait;

/**
 * Help you to run the test into the TAO Context
 * @package tao
 * @deprecated
 */
abstract class TaoPhpUnitTestRunner extends GenerisPhpUnitTestRunner implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use KeyValueMockTrait;

    public const SESSION_KEY = 'TAO_TEST_SESSION';
    /**
     * @var boolean
     */
    private static $connected = false;

    /**
     * Temp fly directory
     * @var Directory
     */
    protected $tempDirectory;

    /**
     * FileSystem id of temp fileSystem
     * @var string
     */
    protected $tempFileSystemId;

    /**
     * shared methods for test initialization
     */
    public static function initTest()
    {

        //connect the API
        if (!self::$connected) {
            \common_session_SessionManager::startSession(new \common_test_TestUserSession());
            self::$connected = true;
        }
    }

    /**
     * At tear down,
     *  - Remove temporary file system created to testing
     */
    protected function tearDown(): void
    {
        $this->removeTempFileSystem();
    }

    /**
     * Return a prophecy of ServiceManager with get($id) calls  will return $service
     * where $id is key of $options, and $service the associated value
     * $service must be a ConfigurableService
     *
     * @param ConfigurableService[] $options
     * @return ServiceLocatorInterface|MockObject as Prophecy
     */
    public function getServiceManagerProphecy(array $options = null)
    {
        return $this->traitGetServiceManagerMock(empty($options) ? [] : $options);
    }

    /**
     * Returns a persistence Manager with a mocked kv persistence
     *
     * @param string $key identifier of the persistence
     * @return \common_persistence_Manager|MockObject
     */
    public function getKvMock($key)
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('sqlite not found, tests skipped.');
        }

        return $this->getKeyValueMock($key);
    }

    /**
     * Get a temp driectory used for testing purpose
     * If not exists, directory filesystem will created (memory if available, or Local)
     *
     * @return Directory
     */
    protected function getTempDirectory()
    {
        if (! $this->tempDirectory) {
            /** @var FileSystemService $fileSystemService */
            $fileSystemService = $this->getServiceManagerProphecy()->get(FileSystemService::SERVICE_ID);
            $this->tempFileSystemId = 'unit-test-' . uniqid();

            $adapters = $fileSystemService->getOption(FileSystemService::OPTION_ADAPTERS);
            $tmpDir = \tao_helpers_File::createTempDir();
            if (class_exists('League\Flysystem\InMemory\InMemoryFilesystemAdapter')) {
                $adapters[$this->tempFileSystemId] = [
                    'class' => InMemoryFilesystemAdapter::class
                ];
            } else {
                $adapters[$this->tempFileSystemId] = [
                    'class' => FileSystemService::FLYSYSTEM_LOCAL_ADAPTER,
                    'options' => ['root' => $tmpDir]
                ];
            }
            $fileSystemService->setOption(FileSystemService::OPTION_ADAPTERS, $adapters);
            $fileSystemService->setOption(FileSystemService::OPTION_FILE_PATH, $tmpDir);
            $fileSystemService->setOption(
                FileSystemService::OPTION_DIRECTORIES,
                [$this->tempFileSystemId => $this->tempFileSystemId]
            );


            $fileSystemService->setServiceLocator($this->getServiceManagerProphecy([
                FileSystemService::SERVICE_ID => $fileSystemService
            ]));

            $this->tempDirectory = $fileSystemService->getDirectory($this->tempFileSystemId);
        }
        return $this->tempDirectory;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeProtectedMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * return inaccessible property value
     * @param type $object
     * @param type $propertyName
     * @return mixed
     */
    protected function getInaccessibleProperty($object, $propertyName)
    {
        $property = new \ReflectionProperty(get_class($object), $propertyName);
        $property->setAccessible(true);
        $value = $property->getValue($object);
        $property->setAccessible(false);
        return $value;
    }
    /**
     * set inaccessible property value
     * @param type $object
     * @param type $propertyName
     * @param type $value
     * @return \oat\tao\test\TaoPhpUnitTestRunner
     */
    protected function setInaccessibleProperty($object, $propertyName, $value)
    {
        $property = new \ReflectionProperty(get_class($object), $propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
        return $this;
    }

    /**
     * At tearDown, unregister tempFileSystem & clean directory
     */
    protected function removeTempFileSystem()
    {
        if ($this->tempDirectory) {
            $this->tempDirectory->deleteSelf();
        }
    }

    /**
     * Remove a local directory recursively
     *
     * @param $dir
     */
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
}
