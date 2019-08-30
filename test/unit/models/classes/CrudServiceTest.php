<?php

namespace oat\tao\test\model\websource;

use oat\generis\test\GenerisTestCase;
use oat\tao\model\OntologyClassService;

class CrudServiceTest extends GenerisTestCase
{
    public function testIsInScope()
    {
        $ontoMock = $this->getOntologyMock();
        $supclass = $ontoMock->getClass('http://unittest/fake#rootClass');
        $class = $ontoMock->getClass('http://unittest/fake#domainClass');
        $service = new CrudServiceTestClass($class);
        $service->setModel($ontoMock);
        
        // Class Instance
        $resource = $class->createInstance('Resource');
        $this->assertTrue($service->isInScope($resource->getUri()));
        // SubClass Instance
        $subClass = $class->createSubClass('IsInScopeSubclass');
        $resource2 = $subClass->createInstance('SubclassResource');
        $this->assertTrue($service->isInScope($resource2->getUri()));
        // Other Class Instance
        $wrongClass = $ontoMock->getClass('http://unittest/fake#rootClassOther');
        $resource3 = $wrongClass->createInstance('WrongResource');
        $this->assertFalse($service->isInScope($resource3->getUri()));
        // Super Class Instance
        $resource4 = $supclass->createInstance('WrongResource');
        $this->assertFalse($service->isInScope($resource4->getUri()));
        
    }
}

class CrudServiceTestClass extends \tao_models_classes_CrudService
{
    private $root;
    
    public function __construct(\core_kernel_classes_Class $class)
    {
        $this->root = $class;
    }
    
    protected function getClassService()
    {
        return new class($this->root) extends OntologyClassService {
            
            private $root;
            
            public function __construct($root)
            {
                $this->root = $root;
            }
            public function getRootClass()
            {
                return $this->root;
            }
        };
    }   
}

