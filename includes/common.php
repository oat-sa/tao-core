<?php
/**
 * Bootstraping
 * @todo create a proper class to bootstrap by context
 */


session_start();



require_once 	dirname(__FILE__). "/config.php";
require_once 	dirname(__FILE__). "/constants.php";

//04 LOADER

/**
 * @function tao_autoload
 * permits to include classes automatically using the ARGOUML class naming convention
 * @param 	string		$pClassName		Name of the class
 */
function tao_autoload($pClassName) {

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