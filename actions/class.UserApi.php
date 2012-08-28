<?php
/**
 * This controller provides a service to allow other sites to authenticate against
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_UserApi extends tao_actions_RemoteServiceModule {

	public function __construct() {
		// make sure the user is autorized
		parent::__construct();
	}
	
	/**
	 * Allows the remote system to change the users password
	 * 
	 * @throws common_Exception
	 */
	public function changePassword() {
		if (!$this->hasRequestParameter('oldpassword')
			|| !$this->hasRequestParameter('newpassword')) {
			throw new common_Exception('Missing paramteres');
		}
		$userService = core_kernel_users_Service::singleton();
		$user = $this->getCurrentUser();
		if (is_null($user) || !$userService->isPasswordValid($this->getRequestParameter('oldpassword'), $user)) {
			return $this->returnFailure('Invalid password');
		}
		
		$userService->setPassword($user, $this->getRequestParameter('newpassword'));
		$this->returnSuccess();
	}
	
	public function setInterfaceLanguage() {
		$success = false;
		if (!$this->hasRequestParameter('lang')) {
			throw new common_Exception('Missing paramteres');
		}
		
		$userService	= core_kernel_users_Service::singleton();
		$user			= $this->getCurrentUser();
		$uiLangResource = tao_helpers_I18n::getLangResourceByCode($this->getRequestParameter('lang'));
		
		if(!is_null($uiLangResource)){
			$success = $user->editPropertyValues(
				new core_kernel_classes_Property(PROPERTY_USER_UILG), $uiLangResource
			);
		} else {
			common_Logger::w('language '.$this->getRequestParameter('lang').' not found');
			return $this->returnFailure(__('Language not supported'));
		}
		
		$this->returnSuccess();
	}
	
	/**
	 * Get detailed information about the current user
	 * 
	 * @throws common_Exception
	 */
	public function getSelfInfo() {
		return $this->returnSuccess(array('info' => self::buildInfo($this->getCurrentUser())));
	}
	
	/**
	 * Returns an array of the information
	 * a remote system might require 
	 * 
	 * @param core_kernel_classes_Resource $user
	 */
	public static function buildInfo(core_kernel_classes_Resource $user) {
		$props = $user->getPropertiesValues(array(
			new core_kernel_classes_Property(PROPERTY_USER_FIRTNAME),			
			new core_kernel_classes_Property(PROPERTY_USER_LASTNAME),
			new core_kernel_classes_Property(PROPERTY_USER_LOGIN),
			new core_kernel_classes_Property(PROPERTY_USER_MAIL),			
			new core_kernel_classes_Property(PROPERTY_USER_UILG),			
			));
			
		$roles = array();
		$roleRes = core_kernel_users_Service::singleton()->getUserRoles($user);
		foreach ($roleRes as $role) {
			$roles[] = array(
				'id'	=> $role->getUri(),
				'label'	=> $role->getLabel()
			);
		}	
		if (isset($props[PROPERTY_USER_UILG]) && is_array($props[PROPERTY_USER_UILG])) {
			$langRes = array_pop($props[PROPERTY_USER_UILG]);
			$lang = (string)$langRes->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE));
		} else {
			$lang = DEFAULT_LANG;
		}
		return array(
			'id'			=> $user->getUri(),
			'login'			=> isset($props[PROPERTY_USER_LOGIN]) ? (string)array_pop($props[PROPERTY_USER_LOGIN]) : '',
			'first_name'	=> isset($props[PROPERTY_USER_FIRTNAME]) ? (string)array_pop($props[PROPERTY_USER_FIRTNAME]) : '',
			'last_name'		=> isset($props[PROPERTY_USER_LASTNAME]) ? (string)array_pop($props[PROPERTY_USER_LASTNAME]) : '',
			'email'			=> isset($props[PROPERTY_USER_MAIL]) ? (string)array_pop($props[PROPERTY_USER_MAIL]) : '',
			'lang'			=> $lang,
			'roles'			=> $roles 
		);
	}

}
?>