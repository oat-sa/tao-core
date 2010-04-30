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

//used for backward compat
$GLOBALS['lang'] = $GLOBALS['default_lang'];


//Authentication and API initialization
if( tao_models_classes_UserService::isASessionOpened()){		//when a user is logged in

	//get the current user data
	$userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
	$currentUser = $userService->getCurrentUser(Session::getAttribute(tao_models_classes_UserService::LOGIN_KEY));

	if(isset($currentUser['login']) && isset($currentUser['password'])){
		
		//connect the API
		core_control_FrontController::connect($currentUser['login'], $currentUser['password'], DATABASE_NAME);
		
		//init the languages
		core_kernel_classes_Session::singleton()->defaultLg = $userService->getDefaultLanguage();
		core_kernel_classes_Session::singleton()->setLg($userService->getUserLanguage($currentUser['login']));
		
		if(isset($currentUser['Uilg'])){
			if(in_array($currentUser['Uilg'], $GLOBALS['available_langs'])){
				$GLOBALS['lang'] = $currentUser['Uilg'];
			}
		}
	}
	unset($currentUser);
}

//initialize I18N
tao_helpers_I18n::init($GLOBALS['lang']);

//Scripts loader
if(!tao_helpers_Request::isAjax()){
	
	//stylesheets to load
	tao_helpers_Scriptloader::addCssFiles(array(
		TAOBASE_WWW . 'css/custom-theme/jquery-ui-1.8.custom.css',
		TAOBASE_WWW . 'js/jwysiwyg/jquery.wysiwyg.css',
		TAOBASE_WWW . 'js/jquery.jqGrid-3.6.4/css/ui.jqgrid.css',
		TAOBASE_WWW . 'css/layout.css',
		TAOBASE_WWW . 'css/form.css',
		
		TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/uploadify.css'
	));
	
	//js golbal vars to export
	tao_helpers_Scriptloader::addJsVars(array(
		'root_url'		=> ROOT_URL,				// -> the app URL (http://www.domain.com or (http://www.domain.com/app)
		'base_url'		=> BASE_URL,				// -> the current extension URL (http://www.domain.com/tao, http://www.domain.com/taoItems)
		'taobase_www'	=> TAOBASE_WWW,				// -> the resources URL of meta extension tao (http://www.domain.com/tao/views/)
		'base_www'		=> BASE_WWW					// -> the resources URL of the current extension (http://www.domain.com/taoItems/views/)
	));
	
	$gridi18nFile = 'js/jquery.jqGrid-3.6.4/js/i18n/grid.locale-'.strtolower($GLOBALS['lang']).'.js';
	if(!file_exists(BASE_PATH. '/views' . $gridi18nFile)){
		$gridi18nFile = 'js/jquery.jqGrid-3.6.4/js/i18n/grid.locale-en.js';
	}
	
	//scripts to load
	tao_helpers_Scriptloader::addJsFiles(array(
		TAOBASE_WWW . 'js/jquery-1.4.2.min.js',
		TAOBASE_WWW . 'js/jquery-ui-1.8.custom.min.js',
		TAOBASE_WWW . 'js/jsTree/jquery.tree.min.js',
		TAOBASE_WWW . 'js/jsTree/plugins/jquery.tree.contextmenu.js',
		TAOBASE_WWW . 'js/jsTree/plugins/jquery.tree.checkbox.js',
		TAOBASE_WWW . 'js/jwysiwyg/jquery.wysiwyg.js',
		TAOBASE_WWW . $gridi18nFile,
		TAOBASE_WWW . 'js/jquery.jqGrid-3.6.4/js/jquery.jqGrid.min.js',
		TAOBASE_WWW . 'js/jquery.numeric.js',
		ROOT_URL 	. '/filemanager/views/js/fmRunner.js',
		ROOT_URL 	. '/filemanager/views/js/jquery.fmRunner.js',
		TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/jquery.uploadify.v2.1.0.min.js',
		TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/swfobject.js',
		
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
}
?>