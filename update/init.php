<?php 
// -- Install bootstrap
$rootDir = dir(dirname(__FILE__).'/../../');
$root = realpath($rootDir->path).'/';
define('TAO_UPDATE_PATH', $root);
define('GENERIS_PATH', $root.'generis/');
set_include_path(get_include_path() . PATH_SEPARATOR . $root. PATH_SEPARATOR . GENERIS_PATH);

function __autoload($class_name) {
	foreach (array(TAO_UPDATE_PATH, GENERIS_PATH) as $dir) {
		$path = str_replace('_', '/', $class_name);
		$file =  'class.' . basename($path). '.php';
		$filePath = $dir . dirname($path) . '/' . $file;
		if (file_exists($filePath)){
			require_once  $filePath;
			break;
		}
		else{
			$file = 'interface.' . basename($path). '.php';
			$filePath = $dir . dirname($path) . '/' . $file;
			if (file_exists($filePath)){
				require_once $filePath;
				break;
			}
		}
	}
}

common_log_Dispatcher::singleton()->init(array(
	array(
		'class'			=> 'SingleFileAppender',
		'threshold'		=> common_Logger::TRACE_LEVEL,
		'file'			=> TAO_UPDATE_PATH.'tao/update/log/update.log',
)));

require_once ('tao/helpers/class.Display.php');
require_once ('tao/helpers/class.Uri.php');

?>