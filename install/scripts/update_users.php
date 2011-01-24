<?php
   /*
    * @todo implement this script in a real update process
    * 1. go to update dir
    * 2. checkout the right version id
    * 3. execute the php scripts and run the sql instructions from the last version to the current version
    */
	
require_once dirname(__FILE__).'/../../includes/raw_start.php';
require_once dirname(__FILE__).'/../utils.php';

$dbWrapper = core_kernel_classes_DbWrapper::singleton(DATABASE_NAME);


$updateFiles = array(
	'00_generisUser.sql'	=> dirname(__FILE__).'/../update/1.2/00_generisUser.sql',
	'00_usersManagement.php'	=> dirname(__FILE__).'/../update/1.2/00_usersManagement.php'
);
foreach($updateFiles as $file => $path){
	if(preg_match("/\.php$/", $file)){
		include $path;
	}
	if(preg_match("/\.sql$/", $file)){
		loadSql($path, $dbWrapper->dbConnector);
	}
}

?>
