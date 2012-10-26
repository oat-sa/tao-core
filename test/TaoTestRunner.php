<?php

require_once dirname(__FILE__).'/../includes/class.Bootstrap.php';
require_once INCLUDES_PATH.'/simpletest/autorun.php';
require_once INCLUDES_PATH.'/ClearFw/core/simpletestRunner/_main.php';
require_once dirname(__FILE__) .'/coverage/coverage.conf.php';

/**
 * Help you to run the test into the TAO Context
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage test
 */
class TaoTestRunner extends TestRunner{
	
	const SESSION_KEY = 'TAO_TEST_SESSION';
    /**
     *
     * @var boolean
     */
    private static $connected = false;


    /**
     * shared methods for test initialization
     */
    public static function initTest(){
        //connect the API
        if(!self::$connected){
            core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);
            self::$connected = true;
        }
    }

	/**
	 * get the list of unit tests
	 * @param null|array $extensions if null all extension, else the list of extensions to look for the tests
	 * @param boolean $recursive if true it checks the subfoldfer
	 * @return array he list of test cases paths
	 */
	public static function getTests($extensions = null, $recursive = false){
		
		$tests = array();
		foreach(scandir(ROOT_PATH) as $extension){
			if(!preg_match("/^\./", $extension)){
				$getTests = false;
				if(is_null($extensions)){
					$getTests = true;
				}
				elseif(is_array($extensions)){
					if(in_array($extension, $extensions)){
						$getTests = true;
					}
				}
				if($getTests){
					$extTestPath = ROOT_PATH . '/' . $extension . '/test';
					$tests = array_merge($tests, self::findTest($extTestPath, $recursive));
				}
			}
		}
		return $tests;
	}
	

}
?>
