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

namespace oat\tao\test\integration\model\mvc\error;

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
    public function testGetResponse()
    {
        $fixtureClass = 'MainResponse';
        $fixtureHttpCode = '404';

        $expected = 'oat\\tao\\model\\mvc\\error\\' . $fixtureClass;

        $exceptionInterpretor = new ExceptionInterpretor();
        $exceptionInterpretor->setServiceLocator($this->getServiceManagerProphecy());
        $this->setInaccessibleProperty($exceptionInterpretor, 'responseClassName', $fixtureClass);
        $this->setInaccessibleProperty($exceptionInterpretor, 'returnHttpCode', $fixtureHttpCode);
        $this->setInaccessibleProperty($exceptionInterpretor, 'trace', 'trace');
        $this->setInaccessibleProperty($exceptionInterpretor, 'exception', new \Exception());

        $this->assertInstanceOf($expected, $exceptionInterpretor->getResponse());
    }
}
