<?php
require_once dirname(__FILE__) . '/../../../tao/test/TestRunner.php';
include_once dirname(__FILE__) . '/../../includes/raw_start.php';

class CleanMassInsertTestCase extends UnitTestCase {

	public function setUp(){

		TestRunner::initTest();
		error_reporting(E_ALL);

		Bootstrap::loadConstants ('tao');
		Bootstrap::loadConstants ('taoGroups');
		Bootstrap::loadConstants ('taoTests');
		Bootstrap::loadConstants ('wfEngine');
		Bootstrap::loadConstants ('taoDelivery');
		
		$this->testService = taoTests_models_classes_TestsService::singleton();
		$this->deliveryService = taoDelivery_models_classes_DeliveryService::singleton();
	}

	public function testRemoveAll(){
		// Remove all subjects
		$subjectClass = new core_kernel_classes_Class(LOCAL_NAMESPACE."#SimulatedTestCaseSubjectClass");
		foreach ($subjectClass->getInstances () as $subject){
			$subject->delete (true);
		}
		$subjectClass->delete (true);
		// Remove all groups
		$groupClass = new core_kernel_classes_Class(LOCAL_NAMESPACE."#SimulatedTestCaseGroupClass");
		foreach ($groupClass->getInstances () as $group){
			$group->delete (true);
		}
		$groupClass->delete (true);
		// Remove all tests
		$testClass = new core_kernel_classes_Class(LOCAL_NAMESPACE."#SimulatedTestCaseTestClass");
		foreach ($testClass->getInstances () as $test){
			$this->testService->deleteTest($test);
		}
		$testClass->delete (true);
		// Remove all deliveries
		$deliveryClass = new core_kernel_classes_Class(LOCAL_NAMESPACE."#SimulatedTestCaseDeliveryClass");
		foreach ($deliveryClass->getInstances () as $delivery){
			$this->deliveryService->deleteDelivery($delivery);
		}
		$deliveryClass->delete (true);
		
		$userService = wfEngine_models_classes_UserService::singleton();
		$users = $userService->getAllUsers(array());
		$systemUsers = array(LOCAL_NAMESPACE.'#superUser', 'http://www.tao.lu/Ontologies/TAO.rdf#installator');
		foreach($users as $user){
		   
		    if(in_array($user->uriResource,$systemUsers)){
		        continue;
		    }
		    $firstnameProp = new core_kernel_classes_Property(PROPERTY_USER_FIRTNAME);
		    $lastnameProp = new core_kernel_classes_Property(PROPERTY_USER_LASTNAME );
		    $firstname = $user->getOnePropertyValue($firstnameProp);
		    $lastname = $user->getOnePropertyValue($lastnameProp);
		    
		    if($firstname == 'Generated'&& $lastname== 'Generated'){
		        $user->delete();
		    }
		}
	}

}
?>
