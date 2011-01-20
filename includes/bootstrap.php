<?php
/**
 * The prepend script is used to Bootstrap the application:
 * It initialize the transversals services: i18n, auth, api, scripts, etc.
 * 
 * @todo refactor it and create a contextual bootstrap sequence (regarding the context: http request, ajax, cli, web service, etc. ) 
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */

require_once 'tao/helpers/class.Uri.php';
require_once 'tao/helpers/class.Display.php';

$GLOBALS['lang'] = $GLOBALS['default_lang'];

//Authentication and API initialization
$userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
$userService->connectCurrentUser();


//initialize the contexts
if(PHP_SAPI == 'cli'){
	tao_helpers_Context::load('SCRIPT_MODE');
}
else{
	tao_helpers_Context::load('APP_MODE');
}


//initialize I18N

if(Session::hasAttribute('ui_lang')){
	$uiLang = Session::getAttribute('ui_lang') ;
}
else{
	$uiLg = null;
	$currentUser = $userService->getCurrentUser(); 
	if(!is_null($currentUser)){
		$uiLg  = $currentUser->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_UILG));
	}
	(!is_null($uiLg)) ? $uiLang = $uiLg->getLabel() : $uiLang = $GLOBALS['default_lang'];
	
}
tao_helpers_I18n::init($uiLang);

//Scripts loader
if(!tao_helpers_Request::isAjax()){
	
	//stylesheets to load
	tao_helpers_Scriptloader::addCssFiles(array(
		TAOBASE_WWW . 'css/custom-theme/jquery-ui-1.8.custom.css',
		TAOBASE_WWW . 'js/jwysiwyg/jquery.wysiwyg.css',
		TAOBASE_WWW . 'js/jquery.jqGrid-3.7.1/css/ui.jqgrid.css',
		TAOBASE_WWW . 'css/style.css',
		TAOBASE_WWW . 'css/layout.css',
		TAOBASE_WWW . 'css/form.css',
		TAOBASE_WWW . 'css/widgets.css'
	));
	
	
	//js golbal vars to export
	tao_helpers_Scriptloader::addJsVars(array(
		'root_url'		=> ROOT_URL,				// -> the app URL (http://www.domain.com or (http://www.domain.com/app)
		'base_url'		=> BASE_URL,				// -> the current extension URL (http://www.domain.com/tao, http://www.domain.com/taoItems)
		'taobase_www'	=> TAOBASE_WWW,				// -> the resources URL of meta extension tao (http://www.domain.com/tao/views/)
		'base_www'		=> BASE_WWW					// -> the resources URL of the current extension (http://www.domain.com/taoItems/views/)
	));
	
	$gridi18nFile = 'js/jquery.jqGrid-3.7.1/js/i18n/grid.locale-'.strtolower(tao_helpers_I18n::getLangCode()).'.js';
	if(!file_exists(BASE_PATH. '/views' . $gridi18nFile)){
		$gridi18nFile = 'js/jquery.jqGrid-3.7.1/js/i18n/grid.locale-en.js';
	}
	
	//scripts to load
	tao_helpers_Scriptloader::addJsFiles(array(
		TAOBASE_WWW . 'js/jquery-1.4.2.min.js',
		TAOBASE_WWW . 'js/jquery-ui-1.8.custom.min.js',
		TAOBASE_WWW . 'js/jsTree/jquery.tree.js',
		TAOBASE_WWW . 'js/jsTree/plugins/jquery.tree.contextmenu.js',
		TAOBASE_WWW . 'js/jsTree/plugins/jquery.tree.checkbox.js',
		TAOBASE_WWW . 'js/jwysiwyg/jquery.wysiwyg.js',
		TAOBASE_WWW . $gridi18nFile,
		TAOBASE_WWW . 'js/jquery.jqGrid-3.7.1/js/jquery.jqGrid.min.js',
		TAOBASE_WWW . 'js/jquery.numeric.js',
		ROOT_URL 	. '/filemanager/views/js/fmRunner.js',
		ROOT_URL 	. '/filemanager/views/js/jquery.fmRunner.js',
		TAOBASE_WWW . 'js/EventMgr.js',
		TAOBASE_WWW . 'js/gateway/Main.js',
		TAOBASE_WWW . 'js/helpers.js',
		TAOBASE_WWW . 'js/uiBootstrap.js',
		TAOBASE_WWW . 'js/uiForm.js',
		TAOBASE_WWW . 'js/generis.tree.js',
		TAOBASE_WWW . 'js/generis.actions.js',
		TAOBASE_WWW . 'js/generis.treeform.js',
		TAOBASE_WWW . 'js/AsyncFileUpload.js'
	));
	
	//ajax file upload works only without HTTP_AUTH
	if(!USE_HTTP_AUTH){
		tao_helpers_Scriptloader::addCssFile(
			TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/uploadify.css'
		);
		tao_helpers_Scriptloader::addJsFiles(array(
			TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/jquery.uploadify.v2.1.0.min.js',
			TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/swfobject.js'
		));
	}
	tao_helpers_Scriptloader::addJsFiles(array(
	//	ROOT_URL 	. '/taiItems/views/js/taoApi/taoApi.min.js',
	));
}
?>