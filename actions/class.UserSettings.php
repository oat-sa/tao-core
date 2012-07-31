<?php
/**
 * This controller provide the actions to manage the user settings
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_UserSettings extends tao_actions_CommonModule {

	/**
	 * @access protected
	 * @var tao_models_classes_UserService
	 */
	protected $userService = null;

	/**
	 * initialize the services
	 * @return
	 */
	public function __construct(){
		parent::__construct();
		$this->userService = tao_models_classes_UserService::singleton();
	}

	public function password(){

		$myFormContainer = new tao_actions_form_UserPassword();
		$myForm = $myFormContainer->getForm();
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				$user = $this->userService->getCurrentUser();
				core_kernel_users_Service::singleton()->setPassword($user, $myForm->getValue('newpassword'));
				$this->setData('message', __('Password changed'));
			}
		}
		$this->setData('formTitle'	, __("Change password"));
		$this->setData('myForm'		, $myForm->render());

		//$this->setView('form.tpl');
		$this->setView('form/settings_user.tpl');
	}
	
	/**
	 * change Proprties of the user
	 * @return void
	 */
	public function properties(){

		$myFormContainer = new tao_actions_form_UserSettings($this->getLangs());
		$myForm = $myFormContainer->getForm();
		if($myForm->isSubmited()){
			if($myForm->isValid()){

				$currentUser = $this->userService->getCurrentUser();

				$uiLangCode 	= $myForm->getValue('ui_lang');
				$dataLangCode 	= $myForm->getValue('data_lang');

				$userSettings = array();

				$uiLangResource = tao_helpers_I18n::getLangResourceByCode($uiLangCode);
				if(!is_null($uiLangResource)){
					$userSettings[PROPERTY_USER_UILG] = $uiLangResource->uriResource;
				}
				$dataLangResource = tao_helpers_I18n::getLangResourceByCode($dataLangCode);
				if(!is_null($dataLangResource)){
					$userSettings[PROPERTY_USER_DEFLG] = $dataLangResource->uriResource;
				}

				if($this->userService->bindProperties($currentUser, $userSettings)){

					tao_helpers_I18n::init($uiLangCode);

					core_kernel_classes_Session::singleton()->setInterfaceLanguage($uiLangCode);
					core_kernel_classes_Session::singleton()->setDataLanguage($dataLangCode);

					$this->setData('message', __('settings updated'));

					$this->setData('reload', true);
				}
			}
		}
		$this->setData('formTitle'	, __("My settings"));
		$this->setData('myForm'	, $myForm->render());

		//$this->setView('form.tpl');
		$this->setView('form/settings_user.tpl');
	}



	/**
	 * get the langage of the current user
	 * @return the lang codes
	 */
	private function getLangs(){
		$session = core_kernel_classes_Session::singleton();
		return array('data_lang' => $session->getDataLanguage(), 'ui_lang' => $session->getInterfaceLanguage());
	}

}
?>