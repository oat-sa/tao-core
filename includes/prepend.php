<?php
/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */

$GLOBALS['lang'] = $GLOBALS['default_lang'];

//when a user is logged in
if( tao_models_classes_UserService::isASessionOpened()){

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

//I18N
tao_helpers_I18n::init($GLOBALS['lang']);

//Scripts loader
if(!tao_helpers_Request::isAjax()){
	
	tao_helpers_Scriptloader::addCssFiles(array(
		TAOBASE_WWW . 'css/custom-theme/jquery-ui-1.8.custom.css',
		TAOBASE_WWW . 'js/jwysiwyg/jquery.wysiwyg.css',
		TAOBASE_WWW . 'js/jquery.jqGrid-3.6.4/css/ui.jqgrid.css',
		TAOBASE_WWW . 'css/layout.css',
		TAOBASE_WWW . 'css/form.css',
		
		TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/uploadify.css'
	));
	
	$gridi18nFile = 'js/jquery.jqGrid-3.6.4/js/i18n/grid.locale-'.strtolower($GLOBALS['lang']).'.js';
	if(!file_exists(BASE_PATH. '/views' . $gridi18nFile)){
		$gridi18nFile = 'js/jquery.jqGrid-3.6.4/js/i18n/grid.locale-en.js';
	}
	
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
		
		TAOBASE_WWW . 'js/eventMgr.js',
		TAOBASE_WWW . 'js/gateway/Main.js',
		
		TAOBASE_WWW . 'js/helpers.js',
		TAOBASE_WWW . 'js/uiBootstrap.js',
		TAOBASE_WWW . 'js/uiForm.js',
		TAOBASE_WWW . 'js/generis.tree.js',
		TAOBASE_WWW . 'js/generis.actions.js',
		TAOBASE_WWW . 'js/generis.treeform.js',
		
		TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/jquery.uploadify.v2.1.0.min.js',
		TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/swfobject.js'
	));
}
?>