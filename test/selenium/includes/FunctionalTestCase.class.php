<?php

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
	
	public function guiBackendLogin() {
		$this->openLocation('backend');
        $this->assertElementPresent('id=login-form');
		$this->type("xpath=//input[@id='login']", 'admin');
		$this->type("xpath=//input[@id='password']", 'admin');
		$this->clickAndWait('id=connect');
	}
	
	public function guiBackendLogout() {
		$this->click("xpath=//a[@title='Logout']");
	}
	
	public function guiFrontendLogin($login, $password) {
		$this->openLocation('frontend');
		$this->assertElementPresent('id="login"');
		$this->assertElementPresent("xpath=//input[@name='login']");
		$this->assertElementPresent("xpath=//input[@name='password']");
		$this->type("xpath=//input[@name='login']", $login);
		$this->type("xpath=//input[@name='password']", $password);
		$this->type('id=connect');
	}
	
	public function guiFrontendLogout() {
		$this->click("xpath=//a[@title='logout']");
	}
	
	public function sysLogin() {
		$userService = core_kernel_users_Service::singleton();
		$userService->login(SYS_USER_LOGIN, SYS_USER_PASS, new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole'));
	}
	
	public function sysLogout() {
		$userService = core_kernel_users_Service::singleton();
		$userService->logout();
	}
	
	public function importRDF($file) {
		
	}
}
?>