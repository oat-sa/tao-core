<?php
/*
 * This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *  
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * 
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 *  Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */
namespace oat\tao\test\unit\model\mvc\error;

use ActionEnforcingException;
use Exception;
use oat\oatbox\user\LoginFailedException;
use oat\tao\model\mvc\error\ExceptionInterpretor;
use oat\tao\test\TaoPhpUnitTestRunner;
use ResolverException;
use tao_models_classes_AccessDeniedException;
use tao_models_classes_FileNotFoundException;
use tao_models_classes_UserException;
/**
 * test for ExceptionInterpretor
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class ExceptionInterpretorTest extends TaoPhpUnitTestRunner 
{

    public function interpretErrorProvider() {
        $userUri = 'toto';
        $action  = 'test'; 
        $module = 'test'; 
        $ext = 'test';
      
        return 
        [
            [new Exception('test message') , 500 , 'test message' , 'MainResponse'],
            [new ResolverException('test message') , 403 , 'test message' , 'RedirectResponse'],
            [new tao_models_classes_UserException('test message') , 403 , 'test message' , 'MainResponse'],
            [new ActionEnforcingException('test message' , $module , $action ) , 404 , 'test message' , 'MainResponse'],
            [new tao_models_classes_FileNotFoundException('test message') , 404 , 'File test message not found' , 'MainResponse'],
            [new LoginFailedException([new Exception('test message')]) , 500 , '' , 'MainResponse'],
        ];
    }

    
    /**
     * 
     * @param type $exception
     * @param type $expectedHttpStatus
     * @param type $expectedTrace
     * @param type $expectedResponseClassName
     * @dataProvider interpretErrorProvider
     */
    public function testInterpretError($exception , $expectedHttpStatus , $expectedTrace , $expectedResponseClassName)  {
        
        $ExceptionInterpretor = new ExceptionInterpretor();
        $this->setInaccessibleProperty($ExceptionInterpretor, 'exception', $exception);
        $this->assertSame($ExceptionInterpretor     , $this->invokeProtectedMethod($ExceptionInterpretor, 'interpretError'));
        $this->assertSame($expectedHttpStatus       , $this->getInaccessibleProperty($ExceptionInterpretor, 'returnHttpCode'));
        $this->assertSame($expectedTrace            , $this->getInaccessibleProperty($ExceptionInterpretor, 'trace'));
        $this->assertSame($expectedResponseClassName, $this->getInaccessibleProperty($ExceptionInterpretor, 'responseClassName'));
    }
    /**
     * 
     */
    public function testSetException() {
        $ExceptionInterpretor = new ExceptionInterpretor();
        $exception = new Exception();
        $this->assertSame($ExceptionInterpretor, $ExceptionInterpretor->setException($exception));
        $this->assertSame($exception,  $this->getInaccessibleProperty($ExceptionInterpretor, 'exception'));
        
    }
    
    public function testGetTrace() {
        $fixtureTrace = 'test toto titi';
        $ExceptionInterpretor = new ExceptionInterpretor();
        $this->setInaccessibleProperty($ExceptionInterpretor, 'trace' , $fixtureTrace);
        $this->assertSame($fixtureTrace, $ExceptionInterpretor->getTrace());
    }
    
    public function testGetHttpCode() {
        $fixtureHttpCode = 407;
        $ExceptionInterpretor = new ExceptionInterpretor();
        $this->setInaccessibleProperty($ExceptionInterpretor, 'returnHttpCode' , $fixtureHttpCode);
        $this->assertSame($fixtureHttpCode, $ExceptionInterpretor->getHttpCode());
    }
    
    public function testGetResponseClassName() {
        $fixtureClassName = 'MainResponse';
        $expected        = 'oat\\tao\\model\\mvc\\error\\' . $fixtureClassName;
        $ExceptionInterpretor = new ExceptionInterpretor();
        $this->setInaccessibleProperty($ExceptionInterpretor, 'responseClassName' , $fixtureClassName);
        $this->assertEquals($expected, $ExceptionInterpretor->getResponseClassName());
    }
    
}
