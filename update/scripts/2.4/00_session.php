<?php
/*
 * This update script adds two new constants in the Generis configuration file
 * for session handling.
 */
$matches = array();
$path = dirname(__FILE__) . '/../../../../generis/';
if (($realpath = realpath($path)) !== false){
	$path = $realpath;
}

if (($content = @file_get_contents($path)) !== false){
	$instanceName = 'tao-' . rand(1000, 9999);
	$sessionName = tao_install_Installator::generateSessionName();
	
	$content .= "\n";
	$content .= "# platform identification\n";
	$content .= "define('GENERIS_INSTANCE_NAME', '${newInstance}');\n";
	$content .= "define('GENERIS_SESSION_NAME', '${sessionName}');\n";
	
	if (file_put_contents($path, $content) === false){
		die("An error occured while writing the Generis configuration file located at '${path}'.\n"
			. "Please make sure it exists and that you have the correct permissions.");
	}
}
else{
	die("The Generis configuration file located at '${path}' cannot be read.\n"
		. "Please make sure it exists and that you have the correct permissions.");
}
?>