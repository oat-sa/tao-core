<?php

require_once dirname(__FILE__) . '/../../generis/common/inc.extension.php';


require_once INCLUDES_PATH.'/simpletest/autorun.php';

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
define("MODULE", "taotrans_demo", true);

/**
 * This class enable you to test the models managment of the tao extension
 *
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package tao
 * @subpackage test
 */
class ServiceTestCase extends UnitTestCase {
	
	/**
	 * @var tao_models_classes_TaoService we share the service instance between the tests
	 */
	protected $taoService = null;
	
	/**
	 * tests initialization
	 */
	public function setUp(){		
		//connection to the API 
		core_control_FrontController::connect(LOGIN, md5(PASS), MODULE);
	}
	
	/**
	 * Test the service factory: dynamical instantiation and single instance serving  
	 * @see tao_models_classes_ServiceFactory::get
	 */
	public function testServiceFactory(){
		
		$this->assertNull($this->taoService);
		
		//test factory instantiation
		$this->taoService = tao_models_classes_ServiceFactory::get('tao_models_classes_TaoService');
		$this->assertIsA($this->taoService, 'tao_models_classes_TaoService');
		
		//test factory singleton
		$taoService2 = tao_models_classes_ServiceFactory::get('tao_models_classes_TaoService');
		$this->assertIdentical($this->taoService, new tao_models_classes_TaoService());
		$this->assertIdentical($taoService2, new tao_models_classes_TaoService());
		$this->assertReference($this->taoService, $taoService2);
		
	}
	
	/**
	 * Test the taoService methods
	 * @see tao_models_classes_TaoService::getLoadedExtensions
	 */
	public function testTaoService(){
		
		$extensions = $this->taoService->getLoadedExtensions();
		$this->assertTrue( is_array($extensions) );
			
		$usualExts = array(
			'taoGroups',
			'taoItems',
			'taoResults',
			'taoSubjects',
			'taoTests'
		);
		foreach($usualExts as $usualExt){
			if(is_dir(GENERIS_BASE_PATH . '/' . $usualExt)){				//if the extension exists in the file system
				$this->assertTrue( in_array($usualExt, $extensions) );		//the service should return it
			}
		}
	}
	
	/**
	 * Test the Service methods from the abtract Service class, 
	 * but using the tao_models_classes_TaoService as a common child to access the methods of the abtract class
	 * @see tao_models_classes_Service
	 */
	public function testAbstractService(){
		
		//test the getOneInstanceBy method on the boolean
		$booleanTrueinstance = $this->taoService->getOneInstanceBy(
			new core_kernel_classes_Class('http://www.tao.lu/Ontologies/generis.rdf#Boolean'),  
			'http://www.tao.lu/Ontologies/generis.rdf#True'
		);
		var_dump(new core_kernel_classes_Resource(GENERIS_TRUE));
		var_dump( $this->taoService,$booleanTrueinstance);
		$this->assertIsA( $booleanTrueinstance, 'core_kernel_classes_Resource');
		$this->assertEqual( strtoupper($booleanTrueinstance->getLabel()), 'TRUE');
			
		$booleanFalseinstance = $this->taoService->getOneInstanceBy(
			new core_kernel_classes_Class('http://www.tao.lu/Ontologies/generis.rdf#Boolean'),  
			'FALSE',
			'label',
			true
		);
		$this->assertIsA( $booleanFalseinstance, 'core_kernel_classes_Resource');
		$this->assertEqual( $booleanFalseinstance->uriResource, 'http://www.tao.lu/Ontologies/generis.rdf#False');
		
		
		//we create a temp object for the needs of the test
		$generisResourceClass = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource');
		$testModelClass = $generisResourceClass->createSubClass('aModel', 'test model');
		$testProperty = $testModelClass->createProperty('aKey', 'test property');
		
		//test the createInstance method 
		$testInstance = $this->taoService->createInstance($testModelClass, 'anInstance');
		$this->assertIsA( $testInstance, 'core_kernel_classes_Resource');
		
		//test the bindProperties method
		$testInstance = $this->taoService->bindProperties(
			$testInstance, 
			array(
				$testProperty->uriResource => array('value' => 'aValue')
			)
		);
		$this->assertIsA( $testInstance, 'core_kernel_classes_Resource');
		$this->assertEqual($testInstance->getUniquePropertyValue($testProperty), 'aValue');
		
		
		//clean them
		$testInstance->delete();
		$testProperty->delete();
		$testModelClass->delete();
	}
	
	public function testExtensions(){
		$extensions = $this->taoService->getLoadedExtensions();
		foreach($extensions as $extension){
			$extTestPath = ROOT_PATH . $extension . '/test';
			if(file_exists($extTestPath)){
				if(is_dir($extTestPath)){
					foreach(scandir($extTestPath) as $file){
						if(preg_match("/TestCase\.php$/", $file)){
							echo "You can run too : <a href='".ROOT_URL.'/'.$extension. "/test/$file'>$file</a> <br>";
						}
					}
				}
			}
		}
	}
}
?>