<?php
require_once dirname(__FILE__) . '/../includes/raw_start.php';

//output regarding the context
function out($msg = ''){
	print $msg;
	print (PHP_SAPI == 'cli') ? "\n" : "<br />";
}
out();
out("Running ".basename(__FILE__));

$exportDir = sys_get_temp_dir();

if(PHP_SAPI == 'cli'){	//from command line
	
	if(isset($_SERVER['argv'][1])){
		$exportDir = $_SERVER['argv'][1]; 
	}
}
else{					//from a browser
	
	if(isset($_GET['exportDir'])){
		$exportDir = $_GET['exportDir']; 
	}
}
if(!is_dir($exportDir)){
	out("$exportDir is not a directory");
	exit;
}

$api = core_kernel_impl_ApiModelOO::singleton();

$nsManager = common_ext_NamespaceManager::singleton();
$namespaces = $nsManager->getAllNamespaces();

foreach($namespaces as $namespace){
	out("Exporting $namespace");
	$rdfData = $api->exportXmlRdf(array($namespace));
	if(empty($rdfData)){
		out("Nothing exported!");
		continue;
	}
	$filename = str_replace('/', '_', str_replace('#', '', $namespace));
	if(!preg_match("/\.rdf$/", $filename)){
		$filename .= '.rdf';
	}
	$path = tao_helpers_File::concat(array($exportDir, $filename));
	if(file_put_contents($path, $rdfData) != false){
		out("Namespace exported at $path");
	}
	else{
		out("Error during the file creation : $path");
	}
	out();
}
?>