<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);\n *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
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
				$userSettings = array();
				
				$uiLang 	= new core_kernel_classes_Resource($myForm->getValue('ui_lang'));
				$dataLang 	= new core_kernel_classes_Resource($myForm->getValue('data_lang'));

				$userSettings[PROPERTY_USER_UILG] = $uiLang;
				$userSettings[PROPERTY_USER_DEFLG] = $dataLang;

				$binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($currentUser);
				
				if($binder->bind($userSettings)){

					$uiLangCode		= tao_models_classes_LanguageService::singleton()->getCode($uiLang);
					$dataLangCode	= tao_models_classes_LanguageService::singleton()->getCode($dataLang);
					
					tao_helpers_I18n::init($uiLangCode);

					core_kernel_classes_Session::singleton()->setInterfaceLanguage($uiLangCode);
					core_kernel_classes_Session::singleton()->setDataLanguage($dataLangCode);

					$this->setData('message', __('Settings updated'));

					$this->setData('reload', true);
				}
			}
		}
		$this->setData('formTitle'	, sprintf(__("My settings (%s)"), $this->userService->getCurrentUser()->getLabel()));
		$this->setData('myForm'	, $myForm->render());

		//$this->setView('form.tpl');
		$this->setView('form/settings_user.tpl');
	}



	/**
	 * get the langage of the current user
	 * @return the lang codes
	 */
	private function getLangs(){
		$currentUser = $this->userService->getCurrentUser();
		$props = $currentUser->getPropertiesValues(array(
			new core_kernel_classes_Property(PROPERTY_USER_UILG),
			new core_kernel_classes_Property(PROPERTY_USER_DEFLG)
		));
		$langs = array();
		if (isset($props[PROPERTY_USER_UILG])) {
			$langs['ui_lang'] = current($props[PROPERTY_USER_UILG])->getUri();
		}
		if (isset($props[PROPERTY_USER_DEFLG])) {
			$langs['data_lang'] = current($props[PROPERTY_USER_DEFLG])->getUri();
		}
		return $langs; 
	}

}
?>