<?php

namespace oat\tao\test\integration;

use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\oatbox\filesystem\FileSystemService;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class FileStorageTestCase extends GenerisPhpUnitTestRunner
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

}