<?php

namespace oat\tao\test\integration\model\metadata\import;

use oat\generis\test\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\metadata\import\OntologyMetadataImporter;
use oat\tao\model\metadata\injector\Injector;

class OntologyMetadataTest extends TestCase
{

    public function testImport()
    {
        try {
            $mock = $this->getMockBuilder(OntologyMetadataImporter::class)
                ->disableOriginalConstructor()
                ->setMethods(['getInjectors', 'getResource'])
                ->getMock();

            $mock->expects($this->exactly(3))
                ->method('getInjectors')
                ->willReturn([$this->getInjectorMockery()]);

            $mock->expects($this->any())
                ->method('getResource')
                ->willReturn($this->getResourceMockery());

            $handle = $this->getCsvResource();
            $headers = fgetcsv($handle);

            while (($line = fgetcsv($handle)) !== false) {
                $lineWithHeaders = array_combine($headers, $line);
                $data = [ 'resourceUri' => $lineWithHeaders ];

                $report = $mock->import($data);
                $this->assertFalse($report->containsError());
            }

            fclose($handle);
        } catch (\Exception $e) {
            $this->fail('Import method fail with message: ' . $e->getMessage());
        }

    }

    protected function getResourceMockery()
    {
        $resource = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->setConstructorArgs(['uri-test-' . uniqid()])
            ->setMethods(['exists'])
            ->getMock();
        $resource->expects($this->any())
            ->method('exists')
            ->willReturn(true);
        return $resource;
    }

    protected function getInjectorMockery()
    {
        $injector = $this->getMockForAbstractClass(Injector::class, [], '', false, true, true, ['read', 'write']);

        $injector->expects($this->any())
            ->method('read')
            ->willReturn(['label'=>'labelFixture']);

        $injector->expects($this->any())
            ->method('write')
            ->willReturn(true);
        return $injector;
    }

    public function testInvalidResourceKey()
    {
        $handle = $this->getCsvResource();
        $headers = fgetcsv($handle);

        $line = fgetcsv($handle);
        $lineWithHeaders = array_combine($headers, $line);
        $data = [ 'abc' => $lineWithHeaders ];

        $mock = $this->getMockBuilder(OntologyMetadataImporter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInjectors'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getInjectors')
            ->willReturn([]);

        $report = $mock->import($data);

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

    public function testGetInjectorWithoutValidInterface()
    {
        $importer = new MockeryTest_MetadataOntologyImport(array(
            'injectorWithInvalidInterface' => [],
        ));

        $importer->setServiceManager(ServiceManager::getServiceManager());

        $method = new \ReflectionMethod(get_class($importer), 'getInjectors');
        $method->setAccessible(true);

        $this->expectException(InconsistencyConfigException::class);
        $method->invoke($importer);
    }

    public function testGetInjectorNotFound()
    {
        $importer = new MockeryTest_MetadataOntologyImport(array(
            'injectorNotFound' => [
                'class' => Injector::class
            ],
        ));

        $importer->setServiceManager(ServiceManager::getServiceManager());

        $method = new \ReflectionMethod(get_class($importer), 'getInjectors');
        $method->setAccessible(true);

        $this->expectException(InconsistencyConfigException::class);
        $method->invoke($importer);
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