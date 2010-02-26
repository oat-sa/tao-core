<?php
   /*
    * @todo implement this script in a real update process
    * 1. go to update dir
    * 2. checkout the right version id
    * 3. execute the php scripts and run the sql instructions from the last version to the current version
    */
	
if(PHP_SAPI == 'cli'){
	$_SERVER['HTTP_HOST'] = 'http://localhost';
	$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__).'/../..';
}
	
require_once dirname(__FILE__).'/../../generis/common/inc.extension.php';	
require_once dirname(__FILE__).'/../includes/common.php';

 

$updateFiles = array();
foreach(glob(dirname(__FILE__).'/update/*') as $path){
		$updateFiles[basename($path)] = $path;
}
ksort($updateFiles);	
foreach($updateFiles as $file => $path){
	if(preg_match("/\.php$/", $file)){
		include $path;
	}
	if(preg_match("/\.sql$/", $file)){
		mysql_connect(DATABASE_URL, DATABASE_LOGIN, DATABASE_PASS);
 		mysql_select_db(DATABASE_NAME);
		loadSql($path);
		mysql_close(DATABASE_NAME);
	}
}

function loadSql($pFile) {
	if ($file = @fopen($pFile, "r")){
		$ch = "";

		while (!feof ($file)){
			$line = utf8_decode(fgets($file));

			if (isset($line[0]) && ($line[0] != '#') && ($line[0] != '-')){
				$ch = $ch.$line;
			}
		}

		$requests = explode(";", $ch);
		unset($requests[count($requests)-1]);
		foreach($requests as $request){
			mysql_query($request);
		}

		fclose($file);
	}
	else{
		die("File not found".$pFichier);
	}
}
?>