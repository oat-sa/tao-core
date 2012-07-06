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
class tao_actions_AuthService extends tao_actions_CommonModule {
	
	const ALLOWED_ROLE = CLASS_ROLE_WORKFLOWUSERROLE;

	/**
	 * Allows a remote system to connect a tao User
	 */
	public function login() {
		if (!$this->hasRequestParameter('username') || !$this->hasRequestParameter('password')) {
			throw new common_Exception('Missing paramteres');
		}
		$userService = core_kernel_users_Service::singleton();
		$user = $userService->getOneUser($this->getRequestParameter('username'), new core_kernel_classes_Class(self::ALLOWED_ROLE));
		if (!is_null($user)) {
			$correct = $userService->isPasswordValid($this->getRequestParameter('password'), $user);
		} else {
			$correct = false;
		}
		
		
		if ($correct) {
			echo json_encode(array(
				'success'	=> true,
				'info'		=> $this->buildInfo($user),
				'token'		=> $this->buildToken($user)
			));
		} else {
			echo json_encode(array(
				'success'	=> false,
				'message'	=> __('Login failed')
			));
		}
	}
	
	/**
	 * Allows the remote system to change the users password
	 * 
	 * @throws common_Exception
	 */
	public function changePassword() {
		if (!$this->hasRequestParameter('username') || !$this->hasRequestParameter('oldpassword')) {
			throw new common_Exception('Missing paramteres');
		}
		$userService = core_kernel_users_Service::singleton();
		$user = $userService->getOneUser($this->getRequestParameter('username'), new core_kernel_classes_Class(self::ALLOWED_ROLE));
		if (is_null($user) || !$userService->isPasswordValid($this->getRequestParameter('oldpassword'), $user)) {
			throw new common_Exception('Invalid password');
		}
		
		
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
		
		throw new common_Exception('Not yet implemented');
	}
	
	/**
	 * Returns an array of the information
	 * a remote system might require 
	 * 
	 * @param core_kernel_classes_Resource $user
	 */
	private function buildInfo(core_kernel_classes_Resource $user) {
		$roles = array();
		foreach (core_kernel_users_Service::singleton()->getUserRoles($user) as $role) {
			$roles[] = $role->getUri();
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
	
	/**
	 * This function should build an authentification token for the user
	 * This function is NOT yet secure
	 * 
	 * @param core_kernel_classes_Resource $user
	 * @return returns a token string
	 */
	private function buildToken(core_kernel_classes_Resource $user) {
		return md5($user->getUri().'secret');
	}
}
?>