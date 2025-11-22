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
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use PHPUnit\Framework\TestCase;

/**
 *@Annotation
 */
class AnnotatedDescription
{
    public $value;
    public $type;
    public $desc;
}

/**
 * @AnnotatedDescription("The class demonstrates the use of annotations")
 */
class AnnotationDemo
{
    /**
     * @AnnotatedDescription("The property is made private for a subtle reason")
     */
    private $property = "I am a private property!";

    /**
     * @AnnotatedDescription(desc ="The property is made private for a subtle reason", type="getter")
     */
    public function getProperty()
    {
        return $this->getProperty();
    }

    /**
     * @AnnotatedDescription("allow", type="{id: READ}")
     * @AnnotatedDescription("allow", type="{uri: WRITE}")
     */
    public function multipleRights()
    {
    }
}

/**
 * Just to show how it works and to check that nothing changed and works as expected
 * Class AnnotationReaderTest
 * @package oat\tao\test\unit\model\routing
 */
class AnnotationReaderTest extends TestCase
{
    private $annotationReader;

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    protected function setUp(): void
    {
        $this->annotationReader = new AnnotationReader();
    }

    public function testObjectAnnotationReader()
    {
        $annotationDemoObject = new AnnotationDemo();
        $reflectionObject = new ReflectionObject($annotationDemoObject);
        $objectAnnotations = $this->annotationReader->getClassAnnotations($reflectionObject);
        self::assertEquals('The class demonstrates the use of annotations', $objectAnnotations[0]->value);
    }

    /**
     * @throws \ReflectionException
     */
    public function testPropertyAnnotationReader()
    {
        //Property Annotations
        $reflectionProperty = new ReflectionProperty('\oat\tao\test\unit\model\routing\AnnotationDemo', 'property');
        $propertyAnnotations = $this->annotationReader->getPropertyAnnotations($reflectionProperty);
        self::assertEquals('The property is made private for a subtle reason', $propertyAnnotations[0]->value);
    }

    /**
     * @throws \ReflectionException
     */
    public function testMethodAnnotationReader()
    {
        // Method Annotations
        $reflectionMethod = new ReflectionMethod('\oat\tao\test\unit\model\routing\AnnotationDemo', 'getProperty');
        $methodAnnotations = $this->annotationReader->getMethodAnnotations($reflectionMethod);
        self::assertNull($methodAnnotations[0]->value);
        self::assertEquals($methodAnnotations[0]->type, 'getter');
        self::assertEquals($methodAnnotations[0]->desc, 'The property is made private for a subtle reason');
    }

    /**
     * @throws \ReflectionException
     */
    public function testClassAnnotationReader()
    {
        //Get class annotation
        $reflectionClass = new ReflectionClass('\oat\tao\test\unit\model\routing\AnnotationDemo');
        $classAnnotations = $this->annotationReader->getClassAnnotations($reflectionClass);
        self::assertEquals('The class demonstrates the use of annotations', $classAnnotations[0]->value);
    }

    /**
     * @throws \ReflectionException
     */
    public function testMultipleRules()
    {
        // Method Annotations
        $reflectionMethod = new ReflectionMethod('\oat\tao\test\unit\model\routing\AnnotationDemo', 'multipleRights');
        $methodAnnotations = $this->annotationReader->getMethodAnnotations($reflectionMethod);
        self::assertEquals('allow', $methodAnnotations[0]->value);
        self::assertEquals('{id: READ}', $methodAnnotations[0]->type);

        self::assertEquals('allow', $methodAnnotations[1]->value);
        self::assertEquals('{uri: WRITE}', $methodAnnotations[1]->type);
    }
}
