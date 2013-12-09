<?php
/*  
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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php
require_once dirname(__FILE__) . '/TaoPhpUnitTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 * This class enable you to test the validators
 *
 * @author Joel Bout, <taosupport@tudor.lu>
 * @package tao
 * @subpackage test
 */
class CardinalityTest extends TaoPhpUnitTestRunner {

	/**
	 * tests initialization
	 */
	public function setUp(){
		TaoPhpUnitTestRunner::initTest();
	}

	/**
	 * Test the service factory: dynamical instantiation and single instance serving
	 * @see tao_models_classes_ServiceFactory::get
	 */
	public function testProperties(){
        $propClass = new core_kernel_classes_Class(RDF_PROPERTY);
        foreach ($propClass->getInstances(true) as $property) {
            $widgets = $property->getPropertyValues(new core_kernel_classes_Property(PROPERTY_WIDGET));
            $this->assertTrue(count($widgets) <= 1, 'Property '.$property->getUri().' has several widgets assigned');
            $domains = $property->getPropertyValues(new core_kernel_classes_Property(RDF_DOMAIN));
            $this->assertTrue(count($domains) <= 1, 'Property '.$property->getUri().' has several domains assigned');
            $ranges = $property->getPropertyValues(new core_kernel_classes_Property(RDFS_RANGE));
            $this->assertTrue(count($ranges) <= 1, 'Property '.$property->getUri().' has several ranges assigned');
        }
	}
}