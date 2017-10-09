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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\test\actionQueue;

use oat\tao\model\actionQueue\implementation\Action;
use oat\tao\test\TaoPhpUnitTestRunner;

/**
 * Class ActionTest
 * @package oat\tao\test\datatable
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class ActionTest extends TaoPhpUnitTestRunner
{

    public function testGetId()
    {

        // Type 1: Simple callback
        $action = new Action('getmypid');
        $this->assertEquals('getmypid', $action->getId());

        // Type 2: Static class method call
        $action = new Action([TestCallableClass::class, 'staticInvoke']);
        $this->assertEquals('oat\tao\test\actionQueue\TestCallableClass::staticInvoke', $action->getId());

        // Type 3: Object method call
        $action = new Action([new TestCallableClass(), 'invoke']);
        $this->assertEquals('oat\tao\test\actionQueue\TestCallableClass::invoke', $action->getId());

        // Type 4: Static class method call (As of PHP 5.2.3)
        $action = new Action(TestCallableClass::class.'::staticInvoke');
        $this->assertEquals('oat\tao\test\actionQueue\TestCallableClass::staticInvoke', $action->getId());

        // Type 5: Relative static class method call (As of PHP 5.3.0)
        $action = new Action([TestCallableClassB::class, 'parent::staticInvoke']);
        $this->assertEquals('oat\tao\test\actionQueue\TestCallableClassB::parent::staticInvoke', $action->getId());

        // Type 6: Objects implementing __invoke can be used as callables (since PHP 5.3)
        $action = new Action(new TestCallableClass());

        $this->assertEquals('oat\tao\test\actionQueue\TestCallableClass::__invoke', $action->getId());
    }

    public function testGetCallable()
    {
        // Type 1: Simple callback
        $action = new Action('getmypid');
        $this->assertEquals('getmypid', $action->getCallable());

        // Type 2: Static class method call
        $action = new Action([TestCallableClass::class, 'staticInvoke']);
        $this->assertEquals(['oat\tao\test\actionQueue\TestCallableClass', 'staticInvoke'], $action->getCallable());
    }

    public function testGetResult()
    {
        $action = new Action('getmypid');
        $action->setResult('result');
        $this->assertEquals('result', $action->getResult());
    }

    public function testSetResult()
    {
        $action = new Action('getmypid');
        $action->setResult('result');
        $this->assertEquals('result', $action->getResult());
    }

    /**
     * @expectedException \oat\tao\model\actionQueue\ActionQueueException
     */
    public function testGetResultException()
    {
        $action = new Action('getmypid');
        $action->getResult();
    }

}

class TestCallableClass
{
    public function __invoke()
    {
        return 'execution result';
    }

    public function invoke()
    {
        return 'execution result';
    }

    public static function staticInvoke()
    {
        return 'execution result';
    }

}

class TestCallableClassB extends TestCallableClass
{

    public static function staticInvoke()
    {
        return 'B execution result';
    }
}