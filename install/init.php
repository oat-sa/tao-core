<?php 
// -- Install bootstrap
$rootDir = dir(dirname(__FILE__).'/../../');
$root = realpath($rootDir->path).'/';
set_include_path(get_include_path() . PATH_SEPARATOR . $root);

function __autoload($class_name) {
	//check if we have a pseudo implementation in /stub
	if (file_exists(dirname(__FILE__).'/stub/'.$class_name.'.php')) {
		
		// use the stub instead of the real class
		require_once dirname(__FILE__).'/stub/'.$class_name.'.php';
	} else {
		
		// include normaly
		$path = str_replace('_', '/', $class_name);
		$file =  'class.' . basename($path). '.php';
	    require_once  dirname($path) . '/' . $file;
	}
}

require_once ('tao/helpers/class.Display.php');
require_once ('tao/helpers/class.Uri.php');
?>