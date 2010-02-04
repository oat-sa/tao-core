<?php

if(PHP_SAPI != 'cli'){
	echo "please run me in command line!";
	exit(1);
}

chdir(dirname(__FILE__));

include 'poextraction/l10n_functions.php';

define('ROOT_PATH', '../../../');

define('MANIFEST_FILE_NAME', 'manifest.php');
define('LOCAL_DIR_NAME', 'locales');
define('PO_FILE_NAME', 'messages.po');

$extensions = array();

foreach(scandir(ROOT_PATH) as $file){
	
	$extDir = ROOT_PATH . $file;
	
	if(is_dir($extDir)){
		if(file_exists($extDir . '/' . MANIFEST_FILE_NAME)){
			
			$localDir = $extDir. '/' . LOCAL_DIR_NAME;
			
			$langs = array();
			foreach(scandir($localDir) as $localFile){
				if(is_dir($localDir . '/' . $localFile)){
					if(file_exists($localDir . '/' . $localFile . '/' . PO_FILE_NAME)){
						$langs[$localFile] = $localDir . '/' . $localFile;
					}
				}
			}
			
			$extensions[$file] = array(
				'path'	=> ROOT_PATH . $file,
				'langs' => $langs  
			);
		}		
	}
}
foreach($extensions as $extensionName => $extensionData){

	##init vars to run the poextraction script
	$directories	= array(
		$extensionData['path'] . '/actions/',
		$extensionData['path'] . '/helpers/',
		$extensionData['path'] . '/models/',
		$extensionData['path'] . '/views/'
	);
	$extension	= array('php', 'tpl', 'js', 'xml');
	$fichier	= PO_FILE_NAME;
	$empLoc		= $extensionData['path'] . '/' . LOCAL_DIR_NAME . '/';
	
	foreach(array_keys($extensionData['langs']) as $langue){
		
		echo "\n => Extract $langue for $extensionName\n";
		
		include 'poextraction/l10n_update.php';
		
		echo "\n------\n";
	}
}
?>