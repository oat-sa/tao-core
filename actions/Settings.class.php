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
class Settings extends CommonModule {

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
		$this->userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
	}

	/**
	 * render the settings form
	 * @return void
	 */
	public function index(){
		
		$myFormContainer = new tao_actions_form_Settings($this->getLangs());
		$myForm = $myFormContainer->getForm();
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$currentUser = $this->userService->getCurrentUser(Session::getAttribute(tao_models_classes_UserService::LOGIN_KEY));
				$currentUser['Deflg'] = $myForm->getValue('data_lang');
				$currentUser['Uilg'] = $myForm->getValue('ui_lang');
				if($this->userService->saveUser($currentUser)){
					$this->setData('message', __('settings updated'));
					$this->setData('refresh', true);
				}
			}
		}
		$this->setData('myForm', $myForm->render());
		$this->setView('settings.tpl');
	}
	
	/**
	 * get the langage of the current user
	 * @return the lang codes
	 */
	private function getLangs(){
		
		$defaultLang = $this->userService->getDefaultLanguage();
		$dataLang = $defaultLang;
		$uiLang = $defaultLang;
		
		$currentUser = $this->userService->getCurrentUser(Session::getAttribute(tao_models_classes_UserService::LOGIN_KEY));
		if(!is_null($currentUser)){
			if(isset($currentUser['Deflg'])){
				$dataLang = $currentUser['Deflg'];
			}
			if(isset($currentUser['Uilg'])){
				$uiLang = $currentUser['Uilg'];
			}
		}
		
		return array('data_lang' => $dataLang, 'ui_lang' => $uiLang);
	}
	
}
?>