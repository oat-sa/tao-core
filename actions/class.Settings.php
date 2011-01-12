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
class tao_actions_Settings extends tao_actions_CommonModule {

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
				
				if($this->userService->saveUser($currentUser, $userSettings)){
					
					tao_helpers_I18n::init($uiLangCode);
					
					core_kernel_classes_Session::singleton()->setLg($dataLangCode);
					
					$this->setData('message', __('settings updated'));
					
					$this->setData('reload', true);
				}
			}
		}
		$this->setData('myForm', $myForm->render());
		$this->setView('form/settings.tpl');
	}
	
	
	
	/**
	 * get the langage of the current user
	 * @return the lang codes
	 */
	private function getLangs(){
		
		$currentUser = $this->userService->getCurrentUser();
		
		$uiLang   		= $GLOBALS['default_lang'];
		$uiLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_UILG));
		if(!is_null($uiLg) && $uiLg instanceof core_kernel_classes_Resource){
			$uiLang = $uiLg->getLabel();
		}
							
		$dataLang   		= $GLOBALS['default_lang'];
		$dataLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_DEFLG));
		if(!is_null($dataLg) && $dataLg instanceof core_kernel_classes_Resource){
			$dataLang = $dataLg->getLabel();
		}
		
		return array('data_lang' => $dataLang, 'ui_lang' => $uiLang);
	}
	
	
}
?>