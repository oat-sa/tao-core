<?php
require_once dirname(__FILE__) . '/../../tao/test/TestRunner.php';
require_once dirname(__FILE__) . '/../includes/common.php';

/**
 *
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package tao
 * @subpackage test
 */
class EventsServiceTestCase extends UnitTestCase {
	
	/**
	 * 
	 * @var tao_models_classes_EventsService
	 */
	protected $eventsService = null;
	
	/**
	 * tests initialization
	 */
	public function setUp(){		
		TestRunner::initTest();
	}
	
	/**
	 * @see tao_models_classes_ServiceFactory::get
	 * @see tao_models_classes_EventsService::__construct
	 */
	public function testService(){
		
		$eventsService = tao_models_classes_ServiceFactory::get('tao_models_classes_EventsService');
		$this->assertIsA($eventsService, 'tao_models_classes_Service');
		$this->assertIsA($eventsService, 'tao_models_classes_EventsService');
		
		$this->eventsService = $eventsService;
	}
	
	public function testParsing(){
		
		$eventFile = dirname(__FILE__).'/samples/events.xml';
		
		$clientEventList = $this->eventsService->getEventList($eventFile, 'client');
		$this->assertTrue(count($clientEventList) > 0);
		$this->assertEqual($clientEventList['type'], 'catch');
		$this->assertTrue(is_array($clientEventList['list']));
		$this->assertTrue(array_key_exists('click', $clientEventList['list']));
		$this->assertTrue(array_key_exists('keyup', $clientEventList['list']));
		$this->assertTrue(array_key_exists('keypress', $clientEventList['list']));
		
		print "<pre>";
		echo json_encode($clientEventList);
		print "</pre>";
		
		$serverEventList = $this->eventsService->getEventList($eventFile, 'server');
		$this->assertTrue(count($serverEventList) > 0);		
		$this->assertEqual($serverEventList['type'], 'catch');
		$this->assertTrue(is_array($serverEventList['list']));
		$this->assertTrue(array_key_exists('mousemove', $serverEventList['list']));
		$this->assertTrue(array_key_exists('mouseout', $serverEventList['list']));
	}
}
?>