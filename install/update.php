<?php
   /*
    * @todo implement this script in a real update process
    * 1. go to update dir
    * 2. checkout the right version id
    * 3. execute the php scripts and run the sql instructions from the last version to the current version
    */
	
if(PHP_SAPI == 'cli'){

	//from command line
	
	$_SERVER['HTTP_HOST'] = 'http://localhost';
	$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__).'/../..';
	(isset($_SERVER['argv'][1])) ? $version = $_SERVER['argv'][1] : $version = false;
	(isset($_SERVER['argv'][2])) ? $scriptNumber = $_SERVER['argv'][2] : $scriptNumber = false;
}
else{
	
	//from a browser
	
	(isset($_GET['version'])) ? $version = $_GET['version'] : $version = false;
	(isset($_GET['scriptNUmber'])) ? $scriptNumber = $_GET['scriptNUmber'] : $scriptNumber = false;
}

if(!$version){
	echo "Please specify the version to update to";
	exit;
}
	
require_once dirname(__FILE__).'/../../generis/common/inc.extension.php';	
require_once dirname(__FILE__).'/../includes/common.php';
require_once dirname(__FILE__).'/utils.php';

$dbWrapper = core_kernel_classes_DbWrapper::singleton(DATABASE_NAME);


//get the files to update
$pattern = dirname(__FILE__).'/update/'.$version .'/';
if(file_exists($pattern) && is_dir($pattern)){

	if($scriptNumber !== false){
		$pattern .= $scriptNumber;
	}
	$pattern .= '*';
	
	$updateFiles = array();
	foreach(glob($pattern) as $path){
			$updateFiles[basename($path)] = $path;
	}
	ksort($updateFiles);	//sort them by number
	
	
	foreach($updateFiles as $file => $path){
		
		//execute php files
		if(preg_match("/\.php$/", $file)){
			include $path;
		}
		
		//execute SQL queries
		if(preg_match("/\.sql$/", $file)){
			loadSql($path, $dbWrapper->dbConnector);
		}
	}
}
?>
