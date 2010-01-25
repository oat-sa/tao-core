<?php
/**
 * Bootstraping
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * 
 * @todo create a proper class to bootstrap by context
 */

session_start();

require_once 	dirname(__FILE__). "/config.php";
require_once 	dirname(__FILE__). "/constants.php";

set_include_path(get_include_path() . PATH_SEPARATOR . GENERIS_BASE_PATH.'/..');

tao_helpers_Scriptloader::addCssFiles(array(
	BASE_WWW . 'css/custom-theme/jquery-ui-1.7.2.custom.css',
	BASE_WWW . 'js/jwysiwyg/jquery.wysiwyg.css',
	BASE_WWW . 'js/jquery.jqGrid-3.6.2/css/ui.jqgrid.css',
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
	BASE_WWW . 'js/jquery.jqGrid-3.6.2/js/i18n/grid.locale-en.js',
	BASE_WWW . 'js/jquery.jqGrid-3.6.2/js/jquery.jqGrid.min.js',
	BASE_WWW . 'js/jquery.numeric.js',
	BASE_WWW . 'js/ajaxupload.js',
	BASE_WWW . 'js/helpers.js',
	BASE_WWW . 'js/form.js',
	BASE_WWW . 'js/control.js',
	BASE_WWW . 'js/settings.js',
	BASE_WWW . 'js/generis.tree.js',
	BASE_WWW . 'js/generis.treeform.js',
	'/filemanager/views/js/fmRunner.js',
	'/filemanager/views/js/jquery.fmRunner.js'
));

require_once(BASE_PATH.'/helpers/class.Uri.php');
require_once(BASE_PATH.'/helpers/class.Display.php');
?>