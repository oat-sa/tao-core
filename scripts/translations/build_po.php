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

//common extensions
foreach($extensions as $extensionName => $extensionData){

	##init vars to run the poextraction script
	$directories	= array(
		$extensionData['path'] . '/actions/',
		$extensionData['path'] . '/helpers/',
		$extensionData['path'] . '/models/',
		$extensionData['path'] . '/views/'
	);
	$exts	= array('php', 'tpl', 'js', 'xml');
	
	foreach(array_keys($extensionData['langs']) as $lang){
		
		$poFile = $extensionData['path'] . '/' . LOCAL_DIR_NAME . '/' . $lang .'/'.PO_FILE_NAME;
		
		echo "\n => Extract $lang for $extensionName\n";
		
		$strings = getAllStrings($directories, $poFile, $exts);
		
		$empties = array();
		foreach($strings as $key => $value){
			if(empty($value)){
				$empties[$key] = $value;
				unset($strings[$key]);
			}
		}
		if(count($empties) > 0){
			ksort($strings);
			ksort($empties);
			foreach($empties as $key => $value){
				$strings[$key] = $value;
			}
			
			if(writePoFile($poFile, $strings)){
				echo "$poFile updated\n";
			}
		}
		
		echo "------\n";
	}
	
}

if(isset($extensions['tao'])){
	echo "\n => Rebuild tao extension \n";
	
	$taoConcats = array();
	foreach($extensions as $extensionName => $extensionData){
		$structureFile = $extensionData['path'] . '/actions/structure.xml';
		if(file_exists($structureFile)){
			$taoConcats = array_merge($taoConcats, getXmlStrings($structureFile));
		}
	}
	
	foreach(array_keys($extensions['tao']['langs']) as $lang){
		$poFile = $extensions['tao']['path']. '/' . LOCAL_DIR_NAME . '/' . $lang .'/'.PO_FILE_NAME;
		$existingStrings = getPoFile($poFile);
	
		// We should look here if these entries are not already set from a previous translation !
		foreach ($taoConcats as $k => $s) {
			if (!array_key_exists($k, $existingStrings)) {
				$existingStrings[$k] = $s;
			}
		}
		
		//$strings = array_merge($existingStrings, $taoConcats);
		if(writePoFile($poFile, $existingStrings)){
			echo "$poFile updated\n";
		}
	}
	
	echo "------\n";
}

//UTR
$utrPath = ROOT_PATH .'taoResults/models/ext/utrv1';
if(file_exists($utrPath)){
	
	$directories	= array(
		$utrPath . '/classes/',
		$utrPath . '/view/'
	);
	$exts	= array('php', 'js');
	$localDir = $utrPath . '/view/' . LOCAL_DIR_NAME;
	$langs = array();
	foreach(scandir($localDir) as $localFile){
		if(is_dir($localDir . '/' . $localFile)){
			if(file_exists($localDir . '/' . $localFile . '/' . PO_FILE_NAME)){
				$langs[$localFile] = $localDir . '/' . $localFile;
			}
		}
	}
	foreach(array_keys($langs) as $lang){
		
		$poFile = $utrPath . '/view/' . LOCAL_DIR_NAME . '/' . $lang .'/'.PO_FILE_NAME;
		
		echo "\n => Extract $lang for UTR\n";
		
		$strings = getAllStrings($directories, $poFile, $exts);
		
		$empties = array();
		foreach($strings as $key => $value){
			if(empty($value)){
				$empties[$key] = $value;
				unset($strings[$key]);
			}
		}
		if(count($empties) > 0){
			ksort($strings);
			ksort($empties);
			foreach($empties as $key => $value){
				$strings[$key] = $value;
			}
			
			if(writePoFile($poFile, $strings)){
				echo "$poFile updated";
			}
		}
		
		echo "\n------\n";
	}
	
}

//WATER PHENIX
$wpPath =  ROOT_PATH .'taoItems/models/ext/itemAuthoring/waterphenix/core';
if(file_exists($wpPath)){
	
	$directories	= array(
		$wpPath . '/js/',
		$wpPath . '/view/'
	);
	$exts	= array('php', 'js', 'ejs');
	$localDir = $wpPath . '/' . LOCAL_DIR_NAME;
	$langs = array();
	foreach(scandir($localDir) as $localFile){
		if(is_dir($localDir . '/' . $localFile)){
			if(file_exists($localDir . '/' . $localFile . '/' . PO_FILE_NAME)){
				$langs[$localFile] = $localDir . '/' . $localFile;
			}
		}
	}
	foreach(array_keys($langs) as $lang){
		
		$poFile = $wpPath . '/' . LOCAL_DIR_NAME . '/' . $lang .'/'.PO_FILE_NAME;
		
		echo "\n => Extract $lang for WATER PHENIX\n";
		
		$strings = getAllStrings($directories, $poFile, $exts);
		
		$empties = array();
		foreach($strings as $key => $value){
			if(empty($value)){
				$empties[$key] = $value;
				unset($strings[$key]);
			}
		}
		if(count($empties) > 0){
			ksort($strings);
			ksort($empties);
			foreach($empties as $key => $value){
				$strings[$key] = $value;
			}
			
			if(writePoFile($poFile, $strings)){
				echo "$poFile updated";
			}
		}
		
		echo "\n------\n";
	}
	
}

?>