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
use oat\generis\test\TestCase;
use ResolverException;
use tao_models_classes_AccessDeniedException;
use tao_models_classes_FileNotFoundException;
use tao_models_classes_UserException;

/**
 * test for ExceptionInterpretor
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class ExceptionInterpretorTest extends TestCase
{

    public function interpretErrorProvider()
    {
        $userUri = 'toto';
        $action = 'test';
        $module = 'test';
        $ext = 'test';

        return
            [
                [new Exception('test message'), 500, 'test message', 'MainResponse'],
                [new ResolverException('test message'), 403, 'test message', 'RedirectResponse'],
                [new tao_models_classes_UserException('test message'), 403, 'test message', 'MainResponse'],
                [new ActionEnforcingException('test message', $module, $action), 404, 'test message', 'MainResponse'],
                [new tao_models_classes_FileNotFoundException('test message'), 404, 'File test message not found', 'MainResponse'],
                [new LoginFailedException([new Exception('test message')]), 500, '', 'MainResponse'],
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
    public function testInterpretError($exception, $expectedHttpStatus, $expectedTrace, $expectedResponseClassName)
    {

        $ExceptionInterpretor = new ExceptionInterpretor();
        $this->setInaccessibleProperty($ExceptionInterpretor, 'exception', $exception);
        $this->assertSame($ExceptionInterpretor, $this->invokeProtectedMethod($ExceptionInterpretor, 'interpretError'));
        $this->assertSame($expectedHttpStatus, $this->getInaccessibleProperty($ExceptionInterpretor, 'returnHttpCode'));
        $this->assertSame($expectedTrace, $this->getInaccessibleProperty($ExceptionInterpretor, 'trace'));
        $this->assertSame($expectedResponseClassName, $this->getInaccessibleProperty($ExceptionInterpretor, 'responseClassName'));
    }

    /**
     *
     */
    public function testSetException()
    {
        $ExceptionInterpretor = new ExceptionInterpretor();
        $exception = new Exception();
        $this->assertSame($ExceptionInterpretor, $ExceptionInterpretor->setException($exception));
        $this->assertSame($exception, $this->getInaccessibleProperty($ExceptionInterpretor, 'exception'));

    }

    public function testGetTrace()
    {
        $fixtureTrace = 'test toto titi';
        $ExceptionInterpretor = new ExceptionInterpretor();
        $this->setInaccessibleProperty($ExceptionInterpretor, 'trace', $fixtureTrace);
        $this->assertSame($fixtureTrace, $ExceptionInterpretor->getTrace());
    }

    public function testGetHttpCode()
    {
        $fixtureHttpCode = 407;
        $ExceptionInterpretor = new ExceptionInterpretor();
        $this->setInaccessibleProperty($ExceptionInterpretor, 'returnHttpCode', $fixtureHttpCode);
        $this->assertSame($fixtureHttpCode, $ExceptionInterpretor->getHttpCode());
    }

    public function testGetResponseClassName()
    {
        $fixtureClassName = 'MainResponse';
        $expected = 'oat\\tao\\model\\mvc\\error\\' . $fixtureClassName;
        $ExceptionInterpretor = new ExceptionInterpretor();
        $this->setInaccessibleProperty($ExceptionInterpretor, 'responseClassName', $fixtureClassName);
        $this->assertEquals($expected, $ExceptionInterpretor->getResponseClassName());
    }

    /**
     * Returns private or protected property value.
     *
     * @param \Object $object
     * @param string $propertyName
     *
     * @return mixed property value
     * @throws \ReflectionException if the class or property does not exist.
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
     * Sets inaccessible property value.
     *
     * @param \Object $object
     * @param string $propertyName
     * @param mixed $value
     *
     * @return $this
     * @throws \ReflectionException if the class or property does not exist.
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
     * Calls protected/private method of a class.
     *
     * @param \Object $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return
     * @throws \ReflectionException if the class or method does not exist.
     */
    public function invokeProtectedMethod($object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
