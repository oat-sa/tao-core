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
class tao_actions_AuthService extends tao_actions_RemoteServiceModule {
	
	const SESSION_DURATION = 43200; // 12 horus

	/**
	 * Allows a remote system to connect a tao User
	 */
	public function login() {
		$user = $this->doLogin();
		if ($user == false) {
			return $this->returnFailure(__('Login failed'));
		} else {
			$this->returnSuccess(array(
				'info'	=> $this->buildInfo($user)
			));
		}
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
	public function getUserInfo() {
		if (!$this->hasRequestParameter('userid')) {
			throw new common_Exception('Missing paramtere');
		}

		$user = new core_kernel_classes_Resource($this->getRequestParameter('userid'));
		return $this->returnSuccess(array('info' => $this->buildInfo($user)));
	}
	
	public function getAllUsers() {
		$service = tao_models_classes_UserService::singleton();
		$users = $service->getAllUsers(array('filteredRoles' => array(CLASS_ROLE_WORKFLOWUSERROLE)));
		$list = array();
		foreach ($users as $user) {
			$props = $user->getPropertiesValues(array(
				new core_kernel_classes_Property(PROPERTY_USER_LOGIN),
//				PROPERTY_USER_UILG,
//				PROPERTY_USER_DEFLG,
				new core_kernel_classes_Property(PROPERTY_USER_MAIL),
				new core_kernel_classes_Property(PROPERTY_USER_FIRTNAME),
				new core_kernel_classes_Property(PROPERTY_USER_LASTNAME),
				));
			$list[] = array(
				'id'	=> $user->getUri(),
				'login'	=> isset($props[PROPERTY_USER_LOGIN])		? (string)array_pop($props[PROPERTY_USER_LOGIN])	: '',
				'mail'	=> isset($props[PROPERTY_USER_MAIL])		? (string)array_pop($props[PROPERTY_USER_MAIL])		: '',
				'first'	=> isset($props[PROPERTY_USER_FIRTNAME])	? (string)array_pop($props[PROPERTY_USER_FIRTNAME])	: '',
				'last'	=> isset($props[PROPERTY_USER_LASTNAME])	? (string)array_pop($props[PROPERTY_USER_LASTNAME])	: ''
			);
		}
		return $this->returnSuccess(array('list' => $list));
	}
	
	public function getAllRoles() {
		$taoManager = new core_kernel_classes_Class(CLASS_ROLE_TAOMANAGER);
		$list = array(
			$taoManager->getUri() => $taoManager->getLabel()
		);
		$abstractRole = new core_kernel_classes_Class(CLASS_ROLE_WORKFLOWUSER);
		foreach ($abstractRole->getInstances() as $concreteRole) {
			$list[$concreteRole->getUri()] = $concreteRole->getLabel();
		}
		return $this->returnSuccess(array('list' => $list));
	}
	
	public function getUserRoles() {
		if (!$this->hasRequestParameter('userid')) {
			throw new common_Exception('Missing paramtere');
		}
		common_Logger::d('user '.$this->getRequestParameter('userid'));
		$user = new core_kernel_classes_Resource($this->getRequestParameter('userid'));
		$uris = array();
		foreach (core_kernel_users_Service::singleton()->getUserRoles($user) as $role) {
			$uris[] = $role->getUri();
		}
		return $this->returnSuccess(array('roles' => $uris));
	}
	
	/**
	 * Returns an array of the information
	 * a remote system might require 
	 * 
	 * @param core_kernel_classes_Resource $user
	 */
	private function buildInfo(core_kernel_classes_Resource $user) {
		$roles = array();
		$roles = core_kernel_users_Service::singleton()->getUserRoles($user);
		foreach ($roles as $role) {
			$roles[] = array(
				'id'	=> $role->getUri(),
				'label'	=> $role->getLabel()
			);
		}
		$props = $user->getPropertiesValues(array(
			new core_kernel_classes_Property(PROPERTY_USER_FIRTNAME),			
			new core_kernel_classes_Property(PROPERTY_USER_LASTNAME),
			new core_kernel_classes_Property(PROPERTY_USER_LOGIN),
			new core_kernel_classes_Property(PROPERTY_USER_MAIL),			
			new core_kernel_classes_Property(PROPERTY_USER_UILG),			
			));
		$lang = array_pop($props[PROPERTY_USER_UILG]);
		return array(
			'id'			=> $user->getUri(),
			'login'			=> isset($props[PROPERTY_USER_LOGIN]) ? (string)array_pop($props[PROPERTY_USER_LOGIN]) : '',
			'first_name'	=> isset($props[PROPERTY_USER_FIRTNAME]) ? (string)array_pop($props[PROPERTY_USER_FIRTNAME]) : '',
			'last_name'		=> isset($props[PROPERTY_USER_LASTNAME]) ? (string)array_pop($props[PROPERTY_USER_LASTNAME]) : '',
			'email'			=> isset($props[PROPERTY_USER_MAIL]) ? (string)array_pop($props[PROPERTY_USER_MAIL]) : '',
			'lang'			=> (string)$lang->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE)),
			'roles'			=> $roles 
		);
	}

}
?>