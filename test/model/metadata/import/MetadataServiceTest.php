<?php

namespace oat\tao\test\model\metadata\import;

use oat\oatbox\service\ServiceManager;
use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\metadata\import\MetadataImporter;
use oat\tao\model\metadata\import\OntologyMetadataImporter;
use oat\tao\model\metadata\injector\Injector;
use oat\tao\test\TaoPhpUnitTestRunner;

class MetadataServiceTest extends TaoPhpUnitTestRunner
{
    /**
     * @todo please remove items ref from here...
     */
    public function testLoading()
    {
        try {
            \common_ext_ExtensionsManager::singleton()->getExtensionById('taoItems');
            $itemService = \taoItems_models_classes_ItemsService::singleton();
            $clazz = $itemService->getRootClass();
            $handle = $this->getCsvResource();
            $headers = fgetcsv($handle);

            $metadataImporter = $this->getMetadataImporter();

            while (($line = fgetcsv($handle)) !== false) {
                $lineWithHeaders = array_combine($headers, $line);
                $item = $itemService->createInstance($clazz, 'unit-test');
                $data = [ $item->getUri() => $lineWithHeaders ];

                $metadataImporter->import($data);
            }

            fclose($handle);
        } catch (\Exception $e) {
            $this->fail('Import method fail with message: ' . $e->getMessage());
        }
        $this->assertTrue(true);
    }

    public function testInvalidResourceKey()
    {
        $handle = $this->getCsvResource();
        $headers = fgetcsv($handle);

        $line = fgetcsv($handle);
        $lineWithHeaders = array_combine($headers, $line);
        $data = [ 'abc' => $lineWithHeaders ];

        $report = $this->getMetadataImporter()->import($data);

        $this->assertTrue($report->containsError());
    }

    public function testGetInjectors()
    {
        $mock = $this->getMockBuilder(MockeryTest_MetadataOntologyImport::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSubService', 'getOptions'])
            ->getMock();

        $injector1 = $this->prophesize(Injector::class);
        $injector1->createInjectorHelpers()->shouldBeCalledTimes(1);

        $injector2 = $this->prophesize(Injector::class);
        $injector2->createInjectorHelpers()->shouldBeCalledTimes(1);

        $mock->expects($this->exactly(1))
            ->method('getOptions')
            ->will($this->returnValue(array('1' => 'one', '2' => 'two')));

        $mock->expects($this->exactly(2))
            ->method('getSubService')
            ->will($this->onConsecutiveCalls(
                $injector1->reveal(),
                $injector2->reveal()
            ));


        $this->assertEquals(2, count($mock->getInjectors()));
    }

    public function testGetInjectorWhitoutValidInterface()
    {
        $importer = new OntologyMetadataImporter(array(
            'injectorWithInvalidInterface' => [],
        ));

        $importer->setServiceManager(ServiceManager::getServiceManager());

        $method = new \ReflectionMethod(get_class($importer), 'getInjectors');
        $method->setAccessible(true);

        $this->setExpectedException(InconsistencyConfigException::class);
        $method->invoke($importer);
    }

    public function testGetInjectorNotFound()
    {
        $importer = new OntologyMetadataImporter(array(
            'injectorNotFound' => [
                'class' => Injector::class
            ],
        ));

        $importer->setServiceManager(ServiceManager::getServiceManager());

        $method = new \ReflectionMethod(get_class($importer), 'getInjectors');
        $method->setAccessible(true);

        $this->setExpectedException(InconsistencyConfigException::class);
        $method->invoke($importer);
    }

    /**
     * @return OntologyMetadataImporter
     */
    protected function getMetadataImporter()
    {
        return ServiceManager::getServiceManager()->get(MetadataImporter::SERVICE_ID);
    }

    /**
     * @return resource
     */
    protected function getCsvResource()
    {
        $path = __DIR__ . '/../../../export/samples/withColNames.csv';
        $resource = fopen($path, 'r');
        if (! $resource) {
            $this->fail('Unable to open csv sample.');
        }
        return $resource;
    }
}

/**
 * To change visibility of getInjectors
 */
class MockeryTest_MetadataOntologyImport extends OntologyMetadataImporter
{
    public function getInjectors() { return parent::getInjectors(); }
}