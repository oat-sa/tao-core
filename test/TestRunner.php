<?php

require_once dirname(__FILE__) . '/../../generis/common/inc.extension.php';
require_once INCLUDES_PATH.'/simpletest/autorun.php';
	
class TestRunner{
	
	public static function initTest(){
		//connect the API
		core_control_FrontController::connect('tao', md5('tao'), DATABASE_NAME);

	}
	
	public static function getTests(){
		$tests = array();
		foreach(scandir(ROOT_PATH) as $extension){
			if(!preg_match("/^\./", $extension)){
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
			}
		}
		return $tests;
	}
}
?>
