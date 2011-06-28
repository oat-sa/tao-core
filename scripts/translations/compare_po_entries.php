<?php

// this script aims at checking if a given po file, identified by an extension name and
// a language tag, is consistant regarding the __() function calls in the source code.

if(PHP_SAPI != 'cli'){
	echo 'please run me in command line!';
	exit(1);
}

chdir(dirname(__FILE__));

include 'poextraction/l10n_functions.php';

define('ROOT_PATH', '../../../');
define('MANIFEST_FILE_NAME', 'manifest.php');
define('LOCAL_DIR_NAME', 'locales');
define('PO_FILE_NAME', 'messages.po');
define('STRUCTURE_DIR_NAME', 'actions');
define('STRUCTURE_FILE_NAME', 'structure.xml');
define('ALTERNATE_STRUCTURE_FILE_NAME', 'users-structure.xml');

if (!isset($argv[1])) {
	die('Please provide an extension name.');
}
else if (!isset($argv[2])) {
	die('Please provide a language tag.');
}

$extensionName = $argv[1];
$languageTag = $argv[2];
$targetPo = ROOT_PATH . $extensionName . '/' . LOCAL_DIR_NAME . '/' . $languageTag . '/' . PO_FILE_NAME;

$extensionPath = array(ROOT_PATH . $extensionName . '/actions/',
					   ROOT_PATH . $extensionName . '/helpers/',
					   ROOT_PATH . $extensionName . '/models/',
					   ROOT_PATH . $extensionName . '/views/');


if (is_file($targetPo)) {
	$exts = array('php', 'tpl', 'js', 'xml');
	$poFile = getPoFile($targetPo);
	ksort($poFile);
	$msgIds = array();
	
	// Crawl source code of this extension.
	foreach ($extensionPath as $path) {
		$msgIds = array_merge($msgIds, parcoursRepertoire($path, $exts));
	}
	
	if ($extensionName == 'tao') {
		// Crawl structure.xml file of all extensions.
		foreach (getExtensionNames(ROOT_PATH, MANIFEST_FILE_NAME, array('generis')) as $extension) {
			$targetStructure = ROOT_PATH . $extension . '/' . STRUCTURE_DIR_NAME . '/' . STRUCTURE_FILE_NAME;
			$alternateTargetStructure = ROOT_PATH . $extension . '/' . STRUCTURE_DIR_NAME . '/' . ALTERNATE_STRUCTURE_FILE_NAME;
			
			if (is_file($targetStructure)) {
				$msgIds = array_merge($msgIds, getXmlStrings($targetStructure));
			}
			else if (is_file($alternateTargetStructure)) {
				$msgIds = array_merge($msgIds, getXmlStrings($alternateTargetStructure));	
			}
		}
	}
	
	ksort($msgIds);
	
	foreach ($poFile as $k => $v) {
		if (!isset($msgIds[$k])) {
			echo "${k} in PO seems to be not used anymore.\n";
		}
	}
}
else {
	die("File ${targetPo} cannot be open.");
}
?>