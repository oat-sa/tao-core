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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2012-2016 Open Assessment Technologies SA
 * 
 */
namespace oat\tao\test;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Memory\MemoryAdapter;
use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Prophecy\Argument;

/**
 * Help you to run the test into the TAO Context
 * @package tao
 * @deprecated
 */
abstract class  TaoPhpUnitTestRunner extends GenerisPhpUnitTestRunner implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

	const SESSION_KEY = 'TAO_TEST_SESSION';
    /**
     *
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
    public static function initTest(){

        //connect the API
        if(!self::$connected){
            \common_session_SessionManager::startSession(new \common_test_TestUserSession());
            self::$connected = true;
        }
    }

    /**
     * At tear down,
     *  - Remove temporary file system created to testing
     */
    protected function tearDown()
    {
        $this->removeTempFileSystem();
    }

    /**
     * Return a prophecy of ServiceManager with get($id) calls  will return $service
     * where $id is key of $options, and $service the associated value
     * $service must be a ConfigurableService
     *
     * @param ConfigurableService[] $options
     * @return ServiceLocatorInterface as Prophecy
     */
    public function getServiceManagerProphecy(array $options = null)
    {
        if (empty($options)) {
            return ServiceManager::getServiceManager();
        }

        $smProphecy = $this->prophesize(ServiceLocatorInterface::class);
        foreach ($options as $key => $service) {
            $smProphecy->get($key)->willReturn($service);
        }
        return $smProphecy->reveal();
    }

    /**
     * Returns a persistence Manager with a mocked kv persistence
     *
     * @param string $key identifier of the persistence
     * @return \common_persistence_Manager
     */
    public function getKvMock($key)
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('sqlite not found, tests skipped.');
        }
        $driver = new \common_persistence_InMemoryKvDriver();
        $persistence = $driver->connect($key, []);
        $pmProphecy = $this->prophesize(\common_persistence_Manager::class);
        $pmProphecy->setServiceLocator(Argument::any())->willReturn(null);
        $pmProphecy->getPersistenceById($key)->willReturn($persistence);
        return $pmProphecy->reveal();
    }

    /**
     * Returns a persistence Manager with a mocked sql persistence
     *
     * @param string $key identifier of the persistence
     * @return \common_persistence_Manager
     */
    public function getSqlMock($key)
    {
        return parent::getSqlMock($key);
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
            if (class_exists('League\Flysystem\Memory\MemoryAdapter')) {
                $adapters[$this->tempFileSystemId] = array(
                    'class' => MemoryAdapter::class
                );
            } else {
                $adapters[$this->tempFileSystemId] = array(
                    'class' => FileSystemService::FLYSYSTEM_LOCAL_ADAPTER,
                    'options' => array('root' => '/tmp/testing')
                );
            }
            $fileSystemService->setOption(FileSystemService::OPTION_ADAPTERS, $adapters);
            $fileSystemService->setOption(FileSystemService::OPTION_FILE_PATH, '/tmp/testing');

            $fileSystemService->setServiceLocator($this->getServiceManagerProphecy(array(
                FileSystemService::SERVICE_ID => $fileSystemService
            )));

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
    public function invokeProtectedMethod($object, $methodName, array $parameters = array())
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
    protected function getInaccessibleProperty($object , $propertyName) {
        $property = new \ReflectionProperty(get_class($object) , $propertyName);
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
    protected function setInaccessibleProperty($object , $propertyName, $value) {
        $property = new \ReflectionProperty(get_class($object) , $propertyName);
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
            /** @var FileSystemService $fileSystemService */
            $fileSystemService = $this->getServiceManagerProphecy()
                ->get(FileSystemService::SERVICE_ID);

            $tempAdapter = $fileSystemService
                ->getFileSystem($this->tempFileSystemId)
                ->getAdapter();

            if ($tempAdapter instanceof Local) {
                $localPath = $this->getInaccessibleProperty($tempAdapter, 'pathPrefix');
                $this->rrmdir($localPath);
            }
        }
    }

    /**
     * Remove a local directory recursively
     *
     * @param $dir
     */
    protected function rrmdir($dir)
    {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file)) {
                $this->rrmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}
