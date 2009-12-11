<?php


//when a user is logged in
if( Session::hasAttribute(tao_models_classes_UserService::AUTH_TOKEN_KEY) && 
	Session::hasAttribute(tao_models_classes_UserService::LOGIN_KEY)){


	
	//get the current user data
	$userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
	$currentUser = $userService->getCurrentUser(Session::getAttribute(tao_models_classes_UserService::LOGIN_KEY));

	var_dump($currentUser);
	exit();
	if(isset($currentUser['login']) && isset($currentUser['password'])){
		
		//connect the API
		core_control_FrontController::connect($currentUser['login'], $currentUser['password'], DATABASE_NAME);
		
		//init the languages
		core_kernel_classes_Session::singleton()->defaultLg = $userService->getDefaultLanguage();
		core_kernel_classes_Session::singleton()->setLg($userService->getUserLanguage($currentUser['login']));
		
	}
	unset($currentUser);
}
?>