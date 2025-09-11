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
 *
 */

namespace oat\tao\test\unit\model\mvc\error;

use PHPUnit\Framework\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\mvc\error\ExceptionInterpreterService;
use oat\tao\model\mvc\error\ExceptionInterpretor;

/**
 * Class ExceptionInterpreterServiceTest
 * @package oat\tao\test\unit\model\mvc\error
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ExceptionInterpreterServiceTest extends TestCase
{
    public function testGetExceptionInterpreter()
    {
        $service = new ExceptionInterpreterService([
            ExceptionInterpreterService::OPTION_INTERPRETERS => [
                AEx::class => AExInt::class,
                BEx::class => BExInt::class,
                \Exception::class => ExceptionInterpretor::class,
                DEx::class => DExInt::class,
            ]
        ]);
        $config = new \common_persistence_KeyValuePersistence([], new \common_persistence_InMemoryKvDriver());
        $service->setServiceLocator(new ServiceManager($config));
        $this->assertEquals(
            ExceptionInterpretor::class,
            get_class($service->getExceptionInterpreter(new \Exception()))
        );
        $this->assertEquals(
            AExInt::class,
            get_class($service->getExceptionInterpreter(new AEx()))
        );
        $this->assertEquals(
            BExInt::class,
            get_class($service->getExceptionInterpreter(new BEx()))
        );
        $this->assertEquals(
            DExInt::class,
            get_class($service->getExceptionInterpreter(new DEx()))
        );

        //closest in hierarchy
        $this->assertEquals(
            BExInt::class,
            get_class($service->getExceptionInterpreter(new CEx()))
        );
        //closest in hierarchy
        $this->assertEquals(
            ExceptionInterpretor::class,
            get_class($service->getExceptionInterpreter(new EEx()))
        );
    }
}

/**
 * Exceptions hierarchy
 */
class AEx extends \Exception
{
}
class BEx extends AEx
{
}
class CEx extends BEx
{
}
class DEx extends CEx
{
}
class EEx extends \Exception
{
}

/**
 * Exception interpreters hierarchy
 */
class AExInt extends ExceptionInterpretor
{
}
class BExInt extends ExceptionInterpretor
{
}
class CExInt extends ExceptionInterpretor
{
}
class DExInt extends ExceptionInterpretor
{
}
