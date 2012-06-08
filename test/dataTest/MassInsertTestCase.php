<?php
require_once dirname(__FILE__) . '/../../../tao/test/TestRunner.php';
include_once dirname(__FILE__) . '/../../includes/raw_start.php';

class MassInsertTestCase extends UnitTestCase {

	/**
	 * CHANGE IT MANNUALLY to see step by step the output
	 * @var boolean
	 */
	const OUTPUT = false;

	/**
	 * @var wfEngine_models_classes_ActivityExecutionService the tested service
	 */
	protected $service = null;

	/**
	 * @var wfEngine_models_classes_UserService
	 */
	protected $userService = null;

	/**
	 * @var core_kernel_classes_Resource
	 */
	protected $subjectService = null;

	/**
	 * @var array
	 */
	protected $languagesUri = array();

	/*
	 * Define the execution parameters
	 */

	// Number of subjects to create
	protected $subjectNumber = 10;
	// Number of groups to create
	protected $groupNumber = 2;
	// Number of languages to create
	protected $testNumber = 1;
		
	// Number of wf User to create
	protected $wfUserNumber = 100 ;

	// Languages available in the TAO platform
	protected $languages = array();
	// Groups available in the TAO platform
	protected $groups = array();
	// Subjects available in the TAO platform
	protected $subjects = array();
	// Tests available in the TAO platform
	protected $tests = array();

	public function setUp(){

		TestRunner::initTest();
		error_reporting(E_ALL);

		Bootstrap::loadConstants ('tao');
		Bootstrap::loadConstants ('taoGroups');
		Bootstrap::loadConstants ('taoTests');
		Bootstrap::loadConstants ('wfEngine');
		Bootstrap::loadConstants ('taoDelivery');

		$classLanguage = new core_kernel_classes_Class(CLASS_LANGUAGES);
		$this->languages = $classLanguage->getInstances();
		$this->testService = taoTests_models_classes_TestsService::singleton();
		$this->deliveryService = taoDelivery_models_classes_DeliveryService::singleton();
		$this->subjectService = taoSubjects_models_classes_SubjectsService::singleton();
	}

	public function testCreateGroups(){

		if ($this->groupNumber){

			//$groupClass = new core_kernel_classes_Class(TAO_GROUP_CLASS);
			$TopGroupClass = new core_kernel_classes_Class(TAO_GROUP_CLASS);
			$groupClass = $TopGroupClass->createSubClass("Simulated (TC)", "Simulated Test Case Group Class", LOCAL_NAMESPACE."#SimulatedTestCaseGroupClass");
			$valueProp = new core_kernel_classes_Property(RDF_VALUE);
			$propertyLabel = new core_kernel_classes_Property(RDFS_LABEL);
			$propertyComment = new core_kernel_classes_Property(RDFS_COMMENT);

			for ($i=1; $i<=$this->groupNumber; $i++){

				// create a Subject
				//$groupInstanceLabel = "Group {$i}";
				//$groupInstanceComment = "Group {$i} comment";
				$groupInstance = $groupClass->createInstance();

				// Add label and comment properties functions of the languages available on the TAO platform
				foreach ($this->languages as $lg){
					$lgCode = $lg->getOnePropertyValue($valueProp);
					$lgLabel = $lg->getLabel();
					$groupInstance->setPropertyValueByLg ($propertyLabel, "Group label{$i} {$lgLabel}={$lgCode}", $lgCode);
					$groupInstance->setPropertyValueByLg ($propertyComment, "Group {$i} Comment {$lgLabel}={$lgCode}", $lgCode);
					
				}
			}

			$this->groups = $groupClass->getInstances ();
			$groupLabels = array();
			
			//check groups for language dependent properties.		

			$expectedArray = array(	'DE' => 'German=DE',
					'FR' => 'French=FR',
					'LU' => 'Luxembourgish=LU',
					'SE' => 'Swedish=SE',
					'EN' => 'English=EN');
			
			//foreach on $this->groups seem to create trouble with
			//testAssociateSubjectGroup test case.
			
			$groupsToTest = $this->groups;
			foreach ($groupsToTest as $group){
				$usedLgs = $group->getUsedLanguages($propertyLabel);
				foreach ($usedLgs as $lg) {
					$result[$lg] = $group->getPropertyValuesByLg($propertyLabel,$lg)->get(0);
				}

				
				foreach ($expectedArray as $k => $v){
					$this->assertTrue(strpos($result[$k], $v));
				}
			}
		}
	}

	public function testCreateSubjects(){

		if ($this->subjectNumber){

			// Create the subject class
			//$subjectClass = new core_kernel_classes_Class(TAO_SUBJECT_CLASS);
			$TopSubjectClass = new core_kernel_classes_Class(TAO_SUBJECT_CLASS);
			$subjectClass = $TopSubjectClass->createSubClass("Simulated (TC)", "Simulated Test Case Subject Class", LOCAL_NAMESPACE."#SimulatedTestCaseSubjectClass");

			// Define usefull properties
			$propertyLoginProp = new core_kernel_classes_Property(PROPERTY_USER_LOGIN);
			$propertyPasswordProp = new core_kernel_classes_Property(PROPERTY_USER_PASSWORD);
			$propertyFirstNameProp = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/generis.rdf#userFirstName');
			$propertyLastNameProp = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/generis.rdf#userLastName');
			$propertyUserDefLgProp = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/generis.rdf#userDefLg');
			$propertyUserUILgProp = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/generis.rdf#userUILg');
			$propertyLabel = new core_kernel_classes_Property(RDFS_LABEL);
			$propertyComment = new core_kernel_classes_Property(RDFS_COMMENT);
			$propertyRdfTypeProp = new core_kernel_classes_Property(RDF_TYPE);
			$valueProp = new core_kernel_classes_Property(RDF_VALUE);

			// Create N subjects
			for ($i=1; $i <= $this->subjectNumber; $i++){

				//if($i<=10) {$i++;continue;}

				$login = "s{$i}";
				$password = "123456";
				$firstName = "first name {$i}";
				$lastName = "last name {$i}";

				$languageUri = 'http://www.tao.lu/Ontologies/TAO.rdf#LangEN';//all in english

				// create a Subject
				//$subjectInstanceLabel = "subject {$i}";
				//$subjectInstanceComment = "subject {$i} comment";
				$subjectInstance = $subjectClass->createInstance();

				// Use setProperty to be compliant with the old API
				$subjectInstance->setPropertyValue ($propertyLoginProp, $login);
				$subjectInstance->setPropertyValue ($propertyPasswordProp, md5($password));
				$subjectInstance->setPropertyValue ($propertyFirstNameProp, $firstName);
				$subjectInstance->setPropertyValue ($propertyLastNameProp, $lastName);
				$subjectInstance->setPropertyValue ($propertyUserDefLgProp, $languageUri);
				$subjectInstance->setPropertyValue ($propertyUserUILgProp, $languageUri);
				$subjectInstance->setType (new core_kernel_classes_Class(CLASS_ROLE_SUBJECT));
/*
				// Commpliant with the new minimal API
				$properties = array(
					$propertyLoginProp->uriResource			=>$login
					, $propertyPasswordProp->uriResource 	=>md5($password)
					, $propertyFirstNameProp->uriResource 	=>$firstName
					, $propertyLastNameProp->uriResource 	=>$lastName
					, $propertyUserDefLgProp->uriResource 	=>$languageUri
					, $propertyUserUILgProp->uriResource 	=>$languageUri
				);
				$subjectInstance->setPropertiesValues ($properties);
				$subjectInstance->setType (new core_kernel_classes_Class(CLASS_ROLE_SUBJECT));
*/
				// Add label and comment properties functions of the languages available on the TAO platform
				
				foreach ($this->languages as $lg){
					$lgCode = $lg->getOnePropertyValue($valueProp);
					$lgLabel = $lg->getLabel();
					$subjectInstance->setPropertyValueByLg ($propertyLabel, "Subject label{$i} {$lgLabel}={$lgCode}", $lgCode);
					$subjectInstance->setPropertyValueByLg ($propertyComment, "Subject {$i} Comment {$lgLabel}={$lgCode}", $lgCode);
				}

			}

			$this->subjects = $subjectClass->getInstances ();
			
			//check subjects for language dependent properties.
			$expectedArray = array(	'DE' => 'German=DE',
					'FR' => 'French=FR',
					'LU' => 'Luxembourgish=LU',
					'SE' => 'Swedish=SE',
					'EN' => 'English=EN');
			
			//foreach on $this->subjects seem to create trouble with
			//testAssociateSubjectGroup test case.
			
			$subjectToTest = $this->subjects;
			foreach ($subjectToTest as $subject){
				$usedLgs = $subject->getUsedLanguages($propertyLabel);
				foreach ($usedLgs as $lg) {
					$result[$lg] = $subject->getPropertyValuesByLg($propertyLabel,$lg)->get(0);
				}
			
			
				foreach ($expectedArray as $k => $v){
					$this->assertTrue(strpos($result[$k], $v));
				}
			}
		}
	}


	public function testAssociateSubjectGroup (){
		if(count($this->groups)){

			// Define usefull properties
			$groupMemberProperty = new core_kernel_classes_Property (TAO_GROUP_MEMBERS_PROP);

			// How many subjects by group
			$step = 1;
			$slice = count($this->subjects)/count($this->groups);
			$i = 0;

			$group = current($this->groups);
			foreach ($this->subjects as $subject){
				$group->setPropertyValue ($groupMemberProperty, $subject->uriResource);

				$i++;
				if ($i>($step*$slice)-1){
					$group = next($this->groups);
					$step++;
				}
			}
		}
	}
	
	public function testCreateWfUsers(){
	    
	    $userService = wfEngine_models_classes_UserService::singleton();
	    $class = new core_kernel_classes_Class(CLASS_ROLE_WORKFLOWUSERROLE);
	    
	    for ($i=1; $i<=$this->wfUserNumber; $i++){
	        $properties = array(
	                        PROPERTY_USER_LOGIN => 'wf'. $i,
	                        PROPERTY_USER_PASSWORD => "123456",
	                        PROPERTY_USER_FIRTNAME => "Generated",
	                        PROPERTY_USER_LASTNAME => "Generated"
	                        
	                        
	                        );
	        $user = $class->createInstance();          
	    	$userService->bindProperties($user,$properties);
	        
	    }
	    
	    
	    
	}

	public function testCreateTests () {

		if (!$this->testNumber){
			return;
		}

		// Define usefull properties
		$testActiveProperty = new core_kernel_classes_Property(TEST_ACTIVE_PROP);

		// Get the test class
		//$testClass = new core_kernel_classes_Class(TAO_TEST_CLASS);
		$TopTestClass = new core_kernel_classes_Class(TAO_TEST_CLASS);
		$testClass = $TopTestClass->createSubClass("Simulated (TC)", "Simulated Test Case Test Class", LOCAL_NAMESPACE."#SimulatedTestCaseTestClass");

		for ($i=0; $i<$this->testNumber; $i++){

			// Create a test instance
			$test = $this->testService->createInstance($testClass, "AutoInsert Test {$i}");

			// Associate an item to the test
			$item = new core_kernel_classes_Resource (common_ext_NamespaceManager::singleton()->getLocalNamespace().'i1292796232039301700');
			$this->testService->setTestItems ($test, array ($item, $item, $item));

			// Active the test
			$test->setPropertyValue($testActiveProperty, GENERIS_TRUE);
		}

		$this->tests = $testClass->getInstances ();

		// Create a delivery
		$topDeliveryClass = new core_kernel_classes_Class(TAO_DELIVERY_CLASS);
		$deliveryClass = $topDeliveryClass->createSubClass("Simulated (TC)", "Simulated Test Case Test Class", LOCAL_NAMESPACE."#SimulatedTestCaseDeliveryClass");
		$delivery = $this->deliveryService->createInstance($deliveryClass, 'AutoInsert Delivery');
		// Set the groups
		$groupsParam = array(); foreach($this->groups as $group) $groupsParam[]= $group->uriResource;
		$this->deliveryService->setDeliveryGroups($delivery, $groupsParam);
		// Set the tests
		$testsParam = array(); foreach($this->tests as $t) $testsParam[]= $t;
		$this->deliveryService->setDeliveryTests($delivery, $testsParam);
	}

}
?>
