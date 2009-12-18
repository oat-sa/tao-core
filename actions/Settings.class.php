<?php
/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class Settings extends CommonModule {

	protected $userService = null;
	
	public function __construct(){
		$this->userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
	}

	/**
	 * render the settings form
	 * @return void
	 */
	public function index(){
		
		$myForm = $this->initSettingsForm();
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$newLang = $myForm->getValue('data_lang');
				$currentUser = $this->userService->getCurrentUser(Session::getAttribute(tao_models_classes_UserService::LOGIN_KEY));
				$currentUser['Deflg'] = $newLang;
				if($this->userService->saveUser($currentUser)){
					core_kernel_classes_Session::singleton()->setLg($newLang);
					$this->setData('message', __('settings updated'));
				}
			}
		}
		$this->setData('myForm', $myForm->render());
		$this->setView('settings.tpl');
	}
	
	/**
	 * create the settings form component
	 * @return tao_helpers_form_Form the form
	 */
	private function initSettingsForm(){
		
		$myForm = tao_helpers_form_FormFactory::getForm('users', array('noRevert' => true));
		
		//@todo manage ui language with .po files
		$uiLangElement = tao_helpers_form_FormFactory::getElement('ui_lang', 'Textbox');
		$uiLangElement->setDescription(__('Interface language'));
		$uiLangElement->setValue('EN');
		$uiLangElement->setAttributes(array("readonly" => "true"));
		$myForm->addElement($uiLangElement);
		
		$currentUser = $this->userService->getCurrentUser(Session::getAttribute(tao_models_classes_UserService::LOGIN_KEY));
		$userLanguage = '';
		if(isset($currentUser['login'])){
			$userLanguage = $this->userService->getUserLanguage($currentUser['login']);
		}
		
		$dataLangElement = tao_helpers_form_FormFactory::getElement('data_lang', 'Textbox');
		$dataLangElement->setDescription(__('Data language'));
		if(!empty($userLanguage)){
			$dataLangElement->setValue($userLanguage);
		}
		else{
			$dataLangElement->setValue($this->userService->getDefaultLanguage());
		}
		$dataLangElement->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
			tao_helpers_form_FormFactory::getValidator('Regex', array('format' => "/^[A-Z]{2,3}$/"))
		));
		$myForm->addElement($dataLangElement);
		
		$myForm->evaluate();
		
		return $myForm;
	}
}
?>