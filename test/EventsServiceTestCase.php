<?php
require_once dirname(__FILE__) . '/../../tao/test/TestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 *
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package tao
 * @subpackage test
 */
class EventsServiceTestCase extends UnitTestCase {
	
	/**
	 * @var tao_models_classes_EventsService
	 */
	protected $eventsService = null;
	
	/**
	 * @var string
	 */
	protected $eventFile;
	
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
		
		$this->eventFile = dirname(__FILE__).'/samples/events.xml';
		
		$this->assertTrue(file_exists($this->eventFile));
	}
	
	public function testParsing(){
		
		$clientEventList = $this->eventsService->getEventList($this->eventFile, 'client');
		$this->assertTrue(count($clientEventList) > 0);
		$this->assertEqual($clientEventList['type'], 'catch');
		$this->assertTrue(is_array($clientEventList['list']));
		$this->assertTrue(array_key_exists('click', $clientEventList['list']));
		$this->assertTrue(array_key_exists('keyup', $clientEventList['list']));
		$this->assertTrue(array_key_exists('keypress', $clientEventList['list']));
		
		$serverEventList = $this->eventsService->getEventList($this->eventFile, 'server');
		$this->assertTrue(count($serverEventList) > 0);		
		$this->assertEqual($serverEventList['type'], 'catch');
		$this->assertTrue(is_array($serverEventList['list']));
		$this->assertTrue(array_key_exists('click', $serverEventList['list']));
	}
	
	public function testTracing(){
		
		$events = array(
			'{"name":"div","type":"click","time":"1288780765981","id":"qunit-fixture"}',
			'{"name":"BUSINESS","type":"TEST","time":"1288780765982","id":"12"}',
			'{"name":"h2","type":"click","time":"1288780766000","id":"qunit-banner"}',
			'{"name":"h1","type":"click","time":"1288780765999","id":"qunit-header"}'
		);
		
		$folder = dirname(__FILE__).'/samples';
		
		$processId = '123456789';
		
		$eventFilter =  $this->eventsService->getEventList($this->eventFile, 'server');
		
		$this->assertTrue($this->eventsService->traceEvent($events, $processId, $folder, $eventFilter));
		
		$this->assertTrue($this->eventsService->traceEvent($events, $processId, $folder));
		
		$this->assertTrue(file_exists($folder. '/' . $processId . '_0.xml'));
		
		foreach(glob($folder . '/'. $processId . '*') as $trace_file){
			if(preg_match('/(xml|lock)$/', $trace_file)){
				unlink($trace_file);
			}
		}
	}
}
?>