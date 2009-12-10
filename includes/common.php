<?php
/**
 * Bootstraping
 * @todo create a proper class to bootstrap by context 
 */

// 01 SESSION

session_start();


// 02 CONF INIT

/**
 * this code is executed before all other script
 */

# constants definition
define('HTTP_GET', 		'GET');
define('HTTP_POST', 	'POST');
define('HTTP_PUT', 		'PUT');
define('HTTP_DELETE', 	'DELETE');
define('HTTP_HEAD', 	'HEAD');

# all error
error_reporting(E_ALL);

# xdebug custom error reporting
if (function_exists("xdebug_enable"))  {
	xdebug_enable();
}


// 03 INCLUDES

require_once 	dirname(__FILE__). "/config.php";
require_once 	dirname(__FILE__). "/constants.php";

//04 LOADER

/**
 * @function tao_autoload
 * permits to include classes automatically using the ARGOUML class naming convention
 * @param 	string		$pClassName		Name of the class
 */
function tao_autoload($pClassName) {
	
	global $__autoload;
	
	if ( isset($__autoload[$pClassName])) {
		require_once $__autoload[$pClassName];
	}
	else {
		$split = explode("_",$pClassName);
		$path = GENERIS_BASE_PATH.'/../';
		for ( $i = 0 ; $i<sizeof($split)-1 ; $i++){
			$path .= $split[$i].'/';
		}
		
	    $filePath = $path . 'class.'.$split[sizeof($split)-1] . '.php';
		if (file_exists($filePath)){
			require_once $filePath;
		}
	}
}

/**
 * @function fw_autoload
 * permits to include classes automatically
 * @param 	string		$pClassName		Name of the class
 */
function fw_autoload($pClassName) {
	if (isset($GLOBALS['classpath']) && is_array($GLOBALS['classpath'])) {
		foreach($GLOBALS['classpath'] as $path) {
			if (file_exists($path. $pClassName . '.class.php')) {
				require_once $path . $pClassName . '.class.php';
    			break;
			}
		}
	}
}

spl_autoload_register("fw_autoload");
spl_autoload_register("tao_autoload");


set_include_path(get_include_path() . PATH_SEPARATOR . GENERIS_BASE_PATH.'/..');


// 05 SCRIPTS
tao_helpers_Scriptloader::addCssFiles(array(

	BASE_WWW . 'css/overcast/jquery-ui-1.7.2.custom.css',
	BASE_WWW . 'js/jwysiwyg/jquery.wysiwyg.css',
	BASE_WWW . 'css/layout.css',
	BASE_WWW . 'css/form.css'

));

tao_helpers_Scriptloader::addJsFiles(array(
	
	BASE_WWW . 'js/jquery-1.3.2.min.js',
	BASE_WWW . 'js/jquery-ui-1.7.2.custom.min.js',
	BASE_WWW . 'js/jsTree/jquery.tree.min.js',
	BASE_WWW . 'js/jsTree/plugins/jquery.tree.contextmenu.js',
	BASE_WWW . 'js/jsTree/plugins/jquery.tree.checkbox.js',
	BASE_WWW . 'js/jwysiwyg/jquery.wysiwyg.js',
	BASE_WWW . 'js/ajaxupload.js',
	BASE_WWW . 'js/form.js',
	BASE_WWW . 'js/control.js',
	BASE_WWW . 'js/generis.tree.js',
	BASE_WWW . 'js/generis.treeform.js'

));
require_once(BASE_PATH.'/helpers/class.Uri.php');
require_once(BASE_PATH.'/helpers/class.Display.php');
?>