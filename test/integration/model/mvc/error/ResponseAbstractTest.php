<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace oat\tao\test\integration\model\mvc\error;

use oat\tao\test\TaoPhpUnitTestRunner;

/**
 * test of ResponseAbstract
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class ResponseAbstractTest extends TaoPhpUnitTestRunner  {
    
    /**
     *
     * @var \oat\tao\model\mvc\error\ResponseAbstract 
     */
    protected $instance;
    
    public function setUp() {
        $this->instance = $this->getMockForAbstractClass('oat\tao\model\mvc\error\ResponseAbstract');
        $this->instance->setServiceLocator($this->getServiceManagerProphecy());
    }
    
    public function testSetHttpCode() {
        $fixtureHttpCode = 404;
        $this->assertSame($this->instance, $this->instance->setHttpCode($fixtureHttpCode));
        $this->assertSame($fixtureHttpCode, $this->getInaccessibleProperty($this->instance, 'httpCode'));
    }
    
    public function chooseRendererProvider() {
        
        return 
        [
            [['text/plain'] , 'oat\tao\model\mvc\error\NonAcceptable'],
            [['application/json' , 'text/html'] , 'oat\tao\model\mvc\error\JsonResponse'],
            [['text/html' , 'application/json'] , 'oat\tao\model\mvc\error\HtmlResponse' ],
        ];
        
    } 
    /**
     * @dataProvider chooseRendererProvider
     * @param array $accept
     * @param string $expected class name
     */
    public function testChooseRenderer($accept , $expected) {
        $this->assertInstanceOf($expected, $this->invokeProtectedMethod($this->instance, 'chooseRenderer' , [$accept]));
    }
    
    public function testSetException() {
        $fixtureException = new \Exception();
        $this->assertSame($this->instance, $this->instance->setException($fixtureException));
        $this->assertSame($fixtureException, $this->getInaccessibleProperty($this->instance, 'exception'));
    }
    
}
