<?php

require_once dirname(__FILE__) . '/../../generis/common/inc.extension.php';
require_once INCLUDES_PATH.'/simpletest/autorun.php';
include_once dirname(__FILE__) .'/../includes/common.php';
	
/**
 * Help you to run the test into the TAO Context
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage test
 */
class TestRunner{
	
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
	 * @return array
	 */
	public static function getTests($extensions = null){
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
					if(file_exists($extTestPath)){
						if(is_dir($extTestPath)){
							foreach(scandir($extTestPath) as $file){
								if(preg_match("/TestCase\.php$/", $file)){
									$tests[] = "$extension/test/$file";
								}
							}
						}
					}
					$contFile = ROOT_PATH . '/' . $extension . '/includes/constants.php';
					if(file_exists($contFile)){
						require_once($contFile);
					}
				}
			}
		}
		return $tests;
	}
}
?>
