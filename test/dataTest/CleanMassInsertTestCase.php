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
		
		$this->testService = tao_models_classes_ServiceFactory::get('Tests');
		$this->deliveryService = tao_models_classes_ServiceFactory::get('taoDelivery_models_classes_DeliveryService');
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
	}

}
?>
