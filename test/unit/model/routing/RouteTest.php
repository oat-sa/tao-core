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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */
namespace oat\tao\test\unit\model\routing;

use Doctrine\Common\Annotations\AnnotationReader;
use oat\tao\model\routing\Route;
use ReflectionMethod;

class FakeClass {
    /**
     * @Route::notFound()
     */
    public function notAllowed()
    {
        echo 'I am not authorized to see it';
    }
}

class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \common_exception_NotFound
     */
    public function testNotFound()
    {
        Route::notFound();
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function testNotFoundByAnnotation()
    {
        $reflectionMethod = new ReflectionMethod(FakeClass::class, 'notAllowed');
        $annotationReader = new AnnotationReader();
        $annotation = $annotationReader->getMethodAnnotation($reflectionMethod, 'Route');
        var_dump($annotation);
    }
}
