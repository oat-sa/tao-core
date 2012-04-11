<?php
require_once dirname(__FILE__) . '/TestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

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
		TestRunner::initTest();
	}
	
	
	
	/**
	 * Test the service factory: dynamical instantiation and single instance serving  
	 * @see tao_models_classes_ServiceFactory::get
	 */
	public function testServiceFactory(){
		
		$this->assertNull($this->taoService);
		
		//test factory instantiation
		$this->taoService = tao_models_classes_TaoService::singleton();
		$this->assertIsA($this->taoService, 'tao_models_classes_TaoService');
		
		$userService = tao_models_classes_UserService::singleton();
		$this->assertIsA($userService, 'tao_models_classes_UserService');
		
		$taoService2 = tao_models_classes_TaoService::singleton();
		$this->assertIsA($taoService2, 'tao_models_classes_TaoService');
		
		//test factory singleton
		$this->assertReference($this->taoService, $taoService2);
		
	}
	
	
	/**
	 * Test the taoService methods, the extensions loading
	 * @see tao_models_classes_TaoService::getLoadedExtensions
	 */
	public function testTaoServiceExtention(){
		
		$extensions = $this->taoService->getLoadedExtensions();
		$this->assertTrue( is_array($extensions) );

		$foundExtensions = array();
		foreach(scandir(ROOT_PATH) as $file){
			if(!preg_match("/^\./", $file) && is_dir(ROOT_PATH.'/'.$file)){
				if(file_exists(ROOT_PATH.'/'.$file.'/manifest.php')){
					$manifest = (include ROOT_PATH.'/'.$file.'/manifest.php');
					$foundExtensions[] = $file;
				}
			}
		}
		
		foreach($extensions as $extension){
			if($this->taoService->isTaoChildExtension($extension)){
				$this->assertTrue( in_array($extension, $foundExtensions) );		//the service should return it
				$structure = $this->taoService->getStructure($extension);
				$this->assertIsA($structure, 'SimpleXMLElement', 'Extention '.$extension.' :%s');

				$this->assertTrue(isset($structure->sections));
				foreach($structure->sections->section as $section){
					$this->assertTrue(isset($section['name']));
					$sectionData = $this->taoService->getStructure($extension, (string)$section['name']);
					$this->assertTrue(isset($sectionData['name']));
					$this->assertTrue(isset($sectionData['url']));
				}
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
			new core_kernel_classes_Class(GENERIS_BOOLEAN),  
			INSTANCE_BOOLEAN_TRUE
		);
		$this->assertIsA( $booleanTrueinstance, 'core_kernel_classes_Resource');
		$this->assertEqual( strtoupper($booleanTrueinstance->getLabel()), 'TRUE');
			
		$booleanFalseinstance = $this->taoService->getOneInstanceBy(
			new core_kernel_classes_Class(GENERIS_BOOLEAN),  
			'FALSE',
			'label',
			true
		);
		$this->assertIsA( $booleanFalseinstance, 'core_kernel_classes_Resource');
		$this->assertEqual( $booleanFalseinstance->uriResource, INSTANCE_BOOLEAN_FALSE);
		
		
		//we create a temp object for the needs of the test
		$generisResourceClass = new core_kernel_classes_Class(GENERIS_RESOURCE);
		$testModelClass = $generisResourceClass->createSubClass('aModel', 'test model');
		$this->assertIsA($testModelClass, 'core_kernel_classes_Class');
		
		$testProperty = $testModelClass->createProperty('aKey', 'test property');
		$this->assertIsA($testProperty, 'core_kernel_classes_Property');
		
		//get the diff between the class and the subclass
		$diffs = $this->taoService->getPropertyDiff($testModelClass, $generisResourceClass);
		$this->assertIsA($diffs, 'array');
		$diffProperty = $diffs[0];
		$this->assertNotNull($diffProperty);
		$this->assertIsA($diffProperty, 'core_kernel_classes_Property');
		$this->assertEqual($testProperty->uriResource, $diffProperty->uriResource);
		
		//test the createInstance method 
		$testInstance = $this->taoService->createInstance($testModelClass, 'anInstance');
		$this->assertIsA( $testInstance, 'core_kernel_classes_Resource');
		
		//get the class from the instance
		$clazz = $this->taoService->getClass($testInstance);
		$this->assertIsA($clazz, 'core_kernel_classes_Class');
		$this->assertEqual($clazz->uriResource, $testModelClass->uriResource);
		
		//test the bindProperties method
		$testInstance = $this->taoService->bindProperties(
			$testInstance, 
			array(
				$testProperty->uriResource => array('value' => 'aValue')
			)
		);
		$this->assertIsA( $testInstance, 'core_kernel_classes_Resource');
		$this->assertEqual($testInstance->getUniquePropertyValue($testProperty)->literal, 'aValue');
		
		
		//clone instance
		$clonedInstance = $this->taoService->cloneInstance($testInstance, $testModelClass);
		$this->assertIsA( $clonedInstance, 'core_kernel_classes_Resource');
		$this->assertNotEqual($clonedInstance->uriResource, $testInstance->uriResource);
		$this->assertEqual($testInstance->getUniquePropertyValue($testProperty), $clonedInstance->getUniquePropertyValue($testProperty));
		
		//get the properties between 2 classes
		$itemClass = new core_kernel_classes_Class(TAO_ITEM_CLASS);
		$itemSubClasses = $itemClass->getSubClasses(false);
		if(count($itemSubClasses) > 0){
			foreach($itemSubClasses as $testClass){ break; }
		}
		else{
			$testClass =$itemClass;
		}
		$foundProp = $this->taoService->getClazzProperties($testClass);
		$this->assertIsA($foundProp, 'array');
        $this->assertTrue(count($foundProp) >= 3, 'the class item or one of is subclasses has less then three properties');
        
		//delete the item class in case it has been created if it was not in the model
		$localNamspace = core_kernel_classes_Session::singleton()->getNamespace();
		if(preg_match("/^".preg_quote($localNamspace, "/")."/", $itemClass->uriResource)){
			$itemClass->delete();
		}
		
		//clean them
		$testInstance->delete();
		$clonedInstance->delete();
		$testProperty->delete();
		$testModelClass->delete();
	}
	
	public function testFileCacheService(){
		$fc = tao_models_classes_cache_FileCache::singleton();
		
		$fc->put("string1", 'testcase1');
		$fromCache = $fc->get('testcase1');
		$this->assertTrue(is_string($fromCache), 'string is not returned as string from FileCache');
		$this->assertEqual($fromCache,  "string1");
		$fc->remove('testcase1');
		
		$fc->put(42, 'testcase2');
		$fromCache = $fc->get('testcase2');
		$this->assertTrue(is_numeric($fromCache), 'numeric is not returned as numeric from FileCache');
		$this->assertEqual($fromCache,  42);
		$fc->remove('testcase2');
		
		$testarr = array(
			'a' => 'astring',
			'b' => 3.1415
		);
		$fc->put($testarr, 'testcase3');
		$fromCache = $fc->get('testcase3');
		$this->assertTrue(is_array($fromCache), 'array is not returned as array from FileCache');
		$this->assertEqual($fromCache,  $testarr);
		$fc->remove('testcase3');
		
		
		$e = new Exception('message');
		$fc->put($e, 'testcase4');
		$fromCache = $fc->get('testcase4');
		$this->assertTrue(is_object($fromCache), 'object is not returned as object from FileCache');
		$this->assertIsA($fromCache, 'Exception');
		$this->assertEqual($e->getMessage(),  $fromCache->getMessage());
		$fc->remove('testcase4');
		
		$badstring = 'abc\'abc\'\'abc"abc""abc\\abc\\\\abc'."abc\n\nabc\l\nabc\l\nabc".'_NULL_é_NUL_'.chr(0).'_';
		$fc->put($badstring, 'testcase5');
		$fromCache = $fc->get('testcase5');
		$this->assertEqual($fromCache, $badstring);
		$fc->remove('testcase5');
	}
	
}
?>