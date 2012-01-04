<?php 
// -- Install bootstrap
$rootDir = dir(dirname(__FILE__).'/../../');
$root = realpath($rootDir->path).'/';
set_include_path(get_include_path() . PATH_SEPARATOR . $root);

function __autoload($class_name) {
	$path = str_replace('_', '/', $class_name);
	$file =  'class.' . basename($path). '.php';
    require_once  dirname($path) . '/' . $file;
}

require_once ('tao/install/stub/common_Logger.php');

require_once ('tao/helpers/class.Display.php');
require_once ('tao/helpers/class.Uri.php');
?>