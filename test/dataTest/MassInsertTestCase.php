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
	protected $subjectNumber = 0;
	// Number of groups to create
	protected $groupNumber = 0;
	// Number of languages to create
	protected $testNumber = 0;

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

		// Get available languages

		// Get all the languages referenced in the TAO platform
		//$filters = array(
		//	RDFS_TYPE => CLASS_LANGUAGES
		//);
		//$clazz = new core_kernel_classes_Class(CLASS_LANGUAGES);
		//$options = array('recursive'	=> false, 'like' => false);
		//$this->languages = $clazz->searchInstances($filters, $options);

		$this->testService = tao_models_classes_ServiceFactory::get('Tests');
		$this->deliveryService = tao_models_classes_ServiceFactory::get('taoDelivery_models_classes_DeliveryService');
		$this->subjectService = tao_models_classes_ServiceFactory::get('taoSubjects_models_classes_SubjectsService');
	}


	//	public function testCreateLanguages(){
	//		return;
	//
	//		if (!$this->createLanguages){
	//			return;
	//		}
	//
	//		/* Créer n languages */
	//
	////		$this->assertTrue(defined('CLASS_LANGUAGES'));
	//		$class = new core_kernel_classes_Class(CLASS_LANGUAGES,__METHOD__);
	////		$this->assertIsA($class, 'core_kernel_classes_Class');
	//
	//		$propertyLevel = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAO.rdf#level');
	//		for ($i=0; $i<$this->languagesNumber; $i++){
	//
	//			$languageLabel = "LG {$i}";
	//			$instance = $class->createInstance($languageLabel, "COMMENT {$languageLabel}");
	//			$instance->setPropertyValue($propertyLevel, $i+1);
	////			$this->assertIsA($instance, 'core_kernel_classes_Resource');
	////			$this->assertEqual($languageLabel, $instance->getLabel());
	//
	//		}
	//
	//	}

	public function testCreateGroups(){
                if($this->groupNumber){
                        $this->assertTrue(defined('TAO_GROUP_CLASS'));
        //		$TopGroupClass = new core_kernel_classes_Class(TAO_GROUP_CLASS);
        //		$groupClass = $TopGroupClass->createSubClass("AutoInsert", "AutoInsert Group Sub Class");
                        $groupClass = new core_kernel_classes_Class(TAO_GROUP_CLASS);
                        $this->assertIsA($groupClass, 'core_kernel_classes_Class');

                        $i=1;
                        $n = $this->groupNumber;

                        // Define the properties used during this process
                        $propertyLabel = new core_kernel_classes_Property(RDFS_LABEL);
                        $propertyComment = new core_kernel_classes_Property(RDFS_COMMENT);

                        // Create N subjects
                        while ($i<=$n){
                                // create a Subject
                                $groupInstanceLabel = "Group {$i}";
                                $groupInstanceComment = "Group {$i} comment";
                                $groupInstance = $groupClass->createInstance($groupInstanceLabel, $groupInstanceComment);
                                $this->assertIsA($groupInstance, 'core_kernel_classes_Resource');

        //			// Add label and comment properties functions of the languages available on the TAO platform
        //			for ($j=0; $j<count($this->languages); $j++){
        //				$lg = $this->languages[$j]->getLabel();
        //				$groupInstance->setPropertyValueByLg ($propertyLabel, "Group label{$i} {$lg}", $lg);
        //				$groupInstance->setPropertyValueByLg ($propertyComment, "Group {$i} Comment {$lg}", $lg);
        //			}

                                $i++;
                        }

                        $this->groups = $groupClass->getInstances ();
                }
	}

	public function testCreateSubjects(){

		if (!$this->subjectNumber){
			return;
		}

		// Create the subject class
		$this->assertTrue(defined('TAO_SUBJECT_CLASS'));
//		$TopSubjectClass = new core_kernel_classes_Class(TAO_SUBJECT_CLASS);
//		$subjectClass = $TopSubjectClass->createSubClass("AutoInsert", "AutoInsert Subject Sub Class");
		$subjectClass = new core_kernel_classes_Class(TAO_SUBJECT_CLASS);
//		$subjectClass = new core_kernel_classes_Resource(CLASS_ROLE_WORKFLOWUSERROLE);
		$this->assertIsA($subjectClass, 'core_kernel_classes_Class');

		$i=1;
		$n = $this->subjectNumber;

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

		// Create N subjects
		while ($i<=$n){
                                
                        //if($i<=10) {$i++;continue;}
                        
			$login = "s{$i}";
			$password = "123456";
			$firstName = "first name {$i}";
			$lastName = "last name {$i}";
			
			$languageUri = 'http://www.tao.lu/Ontologies/TAO.rdf#LangEN';//all in english

			// create a Subject
			$subjectInstanceLabel = "subject {$i}";
			$subjectInstanceComment = "subject {$i} comment";
			$subjectInstance = $subjectClass->createInstance($subjectInstanceLabel, $subjectInstanceComment);
			$this->assertIsA($subjectInstance, 'core_kernel_classes_Resource');

			// Use setProperty to be compliant with the old API
//			$subjectInstance->setPropertyValue ($propertyLoginProp, $login);
//			$subjectInstance->setPropertyValue ($propertyPasswordProp, md5($password));
//			$subjectInstance->setPropertyValue ($propertyFirstNameProp, $firstName);
//			$subjectInstance->setPropertyValue ($propertyLastNameProp, $lastName);
//			$subjectInstance->setPropertyValue ($propertyUserDefLgProp, $languageUri);
//			$subjectInstance->setPropertyValue ($propertyUserUILgProp, $languageUri);
//        	$subjectInstance->setPropertyValue ($propertyRdfTypeProp, CLASS_ROLE_SUBJECT);

			// Commpliant with the new minimal API
			$properties = array(
				$propertyLoginProp->uriResource			=>$login
				, $propertyPasswordProp->uriResource 	=>md5($password)
				, $propertyFirstNameProp->uriResource 	=>$firstName
				, $propertyLastNameProp->uriResource 	=>$lastName
				, $propertyUserDefLgProp->uriResource 	=>$languageUri
				, $propertyUserUILgProp->uriResource 	=>$languageUri
				, $propertyRdfTypeProp->uriResource		=>CLASS_ROLE_SUBJECT
			);
                        $subjectInstance->setPropertiesValues ($properties);                       
                        

			// Add label and comment properties functions of the languages available on the TAO platform
//			for ($j=0; $j<count($this->languages); $j++){
//				$lg = $this->languages[$j]->getLabel();
//				$subjectInstance->setPropertyValueByLg ($propertyLabel, "Subject label{$i} {$lg}", $lg);
//				$subjectInstance->setPropertyValueByLg ($propertyComment, "Subject {$i} Comment {$lg}", $lg);
//			}

			$i++;
		}

		$this->subjects = $subjectClass->getInstances ();
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

	public function testCreateTests () {
                       
                if (!$this->testNumber){
			return;
		}
                
		// Define usefull properties
		$testActiveProperty = new core_kernel_classes_Property(TEST_ACTIVE_PROP);
		
		// Get the test class
		$this->assertTrue(defined('TAO_TEST_CLASS'));
//		$TopTestClass = new core_kernel_classes_Class(TAO_TEST_CLASS);
//		$testClass = $TopTestClass->createSubClass("AutoInsert", "AutoInsert Test Sub Class");
		$testClass = new core_kernel_classes_Class(TAO_TEST_CLASS);
		
		for ($i=0; $i<$this->testNumber; $i++){

			// Create a test instance
			$test = $this->testService->createInstance($testClass, "AutoInsert Test {$i}");

			// Associate an item to the test
			$item = new core_kernel_classes_Resource ('http://tao.local/mytao.rdf#i1292796232039301700');
			$this->testService->setTestItems ($test, array ($item));
			
			// Active the test
			$test->setPropertyValue($testActiveProperty, GENERIS_TRUE);
		}

		$this->tests = $testClass->getInstances ();
		
		// Create a delivery
		$delivery = $this->deliveryService->createInstance(new core_kernel_classes_Class(TAO_DELIVERY_CLASS), 'AutoInsert Delivery');
		// Set the groups
		$groupsParam = array(); foreach($this->groups as $group) $groupsParam[]= $group->uriResource;
		$this->deliveryService->setDeliveryGroups($delivery, $groupsParam);
		// Set the tests
		$testsParam = array(); foreach($this->tests as $t) $testsParam[]= $t;
		$this->deliveryService->setDeliveryTests($delivery, $testsParam);
	}


}
?>