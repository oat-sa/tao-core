<?php
require_once(dirname(__FILE__) . '/includes/start.php');

class SeleniumBackendMainTestCase extends FunctionalTestCase {

	public function setUp()
    {
		parent::setUp();
    }
 
    public function testMainView()
    {
    	$this->guiBackendLogin();
    	
    	// Test if links to all generic extensions are available.
    	$this->assertElementPresent("xpath=//a[@id='extension-nav-taoItems']");
    	$this->assertElementPresent("xpath=//a[@id='extension-nav-taoTests']");
    	$this->assertElementPresent("xpath=//a[@id='extension-nav-taoSubjects']");
    	$this->assertElementPresent("xpath=//a[@id='extension-nav-taoGroups']");
    	$this->assertElementPresent("xpath=//a[@id='extension-nav-taoDelivery']");
    	$this->assertElementPresent("xpath=//a[@id='extension-nav-taoResults']");
    	$this->assertElementPresent("xpath=//a[@id='extension-nav-wfEngine']");
    	
    	$this->guiBackendLogout();
    }
}
?>