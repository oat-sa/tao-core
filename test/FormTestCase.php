<?php

require_once dirname(__FILE__).'/../includes/common.php';
require_once $GLOBALS['inc_path'].'/simpletest/autorun.php';

/**
* @constant login for the generis module you wish to connect to 
*/
define("LOGIN", "demo", true);

/**
* @constant password for the module you wish to connect to 
*/
define("PASS", "demo", true);

/**
* @constant module for the module you wish to connect to 
*/
define("MODULE", "taosubjects", true);

/**
 *
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package tao
 * @subpackage test
 */
class FormTestCase extends UnitTestCase {
	

	
	/**
	 * tests initialization
	 */
	public function setUp(){		
		//connection to the API 
		core_control_FrontController::connect(LOGIN, md5(PASS), MODULE);
		$session = core_kernel_classes_Session::singleton();
		$ontologies = array(
			'http://www.tao.lu/Ontologies/generis.rdf',
			'http://www.tao.lu/Ontologies/TAO.rdf',
			'http://www.tao.lu/Ontologies/TAOBoolean.rdf',
			'http://www.tao.lu/Ontologies/TAOSubject.rdf'
		);
		foreach($ontologies as $ontology ){
			$session->model->loadModel($ontology);
		}
	}
	
	/**
	 * Test the content 
	 * @return 
	 */
	public function testRangeOptions(){

		$options = array();
		
		$property = new core_kernel_classes_Property('http://www.w3.org/2000/01/rdf-schema#range');
		$property->feed();
		
		$range = $property->getRange();
		if($range != null && $range instanceof  core_kernel_classes_Class ){
			foreach($range->getInstances(true) as $rangeInstance){
				$options[$rangeInstance->uriResource] = $rangeInstance->getLabel();
			}
		}
		
		//not empty array
		$this->assertTrue( count($options) > 0);
			
		//Check if the Literal type is in range
		$this->assertTrue( array_key_exists('http://www.w3.org/2000/01/rdf-schema#Literal', $options));
		
		//Check if the Boolean type is in range
		$this->assertTrue( array_key_exists('http://www.tao.lu/Ontologies/generis.rdf#Boolean', $options));
	}

}
?>