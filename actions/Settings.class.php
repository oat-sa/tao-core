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
		
		$myFormContainer = new tao_actions_form_Settings(array('data_lang' => $this->getDataLang()));
		$myForm = $myFormContainer->getForm();
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$newLang = $myForm->getValue('data_lang');
				$currentUser = $this->userService->getCurrentUser(Session::getAttribute(tao_models_classes_UserService::LOGIN_KEY));
				$currentUser['Deflg'] = $newLang;
				if($this->userService->saveUser($currentUser)){
					
					$this->setData('message', __('settings updated'));
				}
			}
		}
		$this->setData('myForm', $myForm->render());
		$this->setView('settings.tpl');
	}
	
	/**
	 * get the langage of the current user
	 * @return the lang code
	 */
	private function getDataLang(){
		$currentUser = $this->userService->getCurrentUser(Session::getAttribute(tao_models_classes_UserService::LOGIN_KEY));
		$userLanguage = '';
		if(isset($currentUser['login'])){
			$userLanguage = $this->userService->getUserLanguage($currentUser['login']);
		}
		if(!empty($userLanguage)){
			return $userLanguage;
		}
		return $this->userService->getDefaultLanguage();
	}
	
}
?>