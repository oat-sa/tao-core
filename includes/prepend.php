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
?>