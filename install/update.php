<?php
   /*
    * @todo implement this script in a real update process
    * 1. go to update dir
    * 2. checkout the right version id
    * 3. execute the php scripts and run the sql instructions from the last version to the current version
    */
if(PHP_SAPI == 'cli'){

	//from command line
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
require_once dirname(__FILE__).'/../includes/raw_start.php';


echo "\nUpdating to $version\n";


//get the files to update
$pattern = dirname(__FILE__).'/update/'.$version .'/';
if(file_exists($pattern) && is_dir($pattern)){
	
	$dbCreator = new tao_install_utils_DbCreator(DATABASE_URL, DATABASE_LOGIN, DATABASE_PASS, SGBD_DRIVER);
	$dbCreator->setDatabase(DATABASE_NAME);
	
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
			echo "running $file\n";
			include $path;
		}
		
		//execute SQL queries
		if(preg_match("/\.sql$/", $file)){
			echo "loading $file\n";
			$dbCreator->load($path);
		}
	}
}
?>
