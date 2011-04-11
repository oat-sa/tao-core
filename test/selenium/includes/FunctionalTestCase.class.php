<?php
require_once('PHPUnit/Extensions/SeleniumTestCase.php');
require_once('config.php');

class FunctionalTestCase extends PHPUnit_Extensions_SeleniumTestCase {
	
	public static $taoUrls = array('backend' 	=> '/tao',
								   'frontend' 	=> '/test',
								   'main' 		=> '/tao/Main/index');
	
	public function setUp() {
		// Default browser will be firefox, essentially for testing.
		// If you need another, override the setUp function in your TestCase and
		// invoke the setBrowser method.
		$this->setBrowser('*firefox');
		$this->setBrowserUrl(TAO_SELENIUM_ROOT_URL);
		$this->setSpeed(TAO_SELENIUM_SPEED);
		$this->setHost(TAO_SELENIUM_HOST);
		$this->setPort(TAO_SELENIUM_PORT);
	}
	
	public function openLocation($name) {
		$this->open(self::$taoUrls[$name]);
	}
	
	public function login() {
		$this->openLocation('backend');
        $this->assertElementPresent('id=login-form');
		$this->type("xpath=//input[@id='login']", 'admin');
		$this->type("xpath=//input[@id='password']", 'admin');
		$this->clickAndWait('id=connect');
	}
	
	public function logout() {
		$this->click("xpath=//a[@title='Logout']");
	}
}
?>