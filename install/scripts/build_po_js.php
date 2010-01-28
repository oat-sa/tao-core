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
define('POJS_FILE_NAME', 'messages_po.js');

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

	foreach($extensionData['langs'] as $code => $path){
		
		$strings = array();
		
		$content = file_get_contents($path . '/' . PO_FILE_NAME);
		$content = substr($content, strstr($content, '"msgid"'));
		$lines = explode("\n", $content);
		foreach($lines as $line){
			if(trim($line) != ''){
				if(preg_match("/^msgid/", $line)){
					$line = preg_replace("/^msgid[\ ]+[\'|\\\"]/", '', ltrim($line));
					$line = preg_replace("/[\'|\\\"]$/", '', rtrim($line));
					$key = $line;
				}
				if(preg_match("/^msgstr/", $line)){
					$line = preg_replace("/^msgstr[\ ]+[\'|\\\"]/", '', ltrim($line));
					$line = preg_replace("/[\'|\\\"]$/", '', rtrim($line));
					$strings[$key] = $line;
				}
			}
		}
		
		$buffer  = "/* auto generated content */\n";
		$buffer .= "/* extesion: $extensionName, lang: $code */\n";
		$buffer .= "var i18n_tr_$code = " . json_encode($strings) . ";";
		if(file_put_contents($path . '/' . POJS_FILE_NAME, $buffer)){
			echo $path . '/' . POJS_FILE_NAME . " created\n";
		}
	}
}
?>