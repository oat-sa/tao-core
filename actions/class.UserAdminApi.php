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
class tao_actions_UserAdminApi extends tao_actions_RemoteServiceModule {
	
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
		return $this->returnSuccess(array('info' => tao_actions_UserApi::buildInfo($user)));
	}
	
	public function getAllUsers() {
		$service = tao_models_classes_UserService::singleton();
		$users = $service->getAllUsers();
		$list = array();
		foreach ($users as $user) {
			$props = $user->getPropertiesValues(array(
				new core_kernel_classes_Property(PROPERTY_USER_LOGIN),
				new core_kernel_classes_Property(PROPERTY_USER_MAIL),
				new core_kernel_classes_Property(PROPERTY_USER_FIRSTNAME),
				new core_kernel_classes_Property(PROPERTY_USER_LASTNAME),
				));
			$list[] = array(
				'id'	=> $user->getUri(),
				'login'	=> isset($props[PROPERTY_USER_LOGIN])		? (string)array_pop($props[PROPERTY_USER_LOGIN])	: '',
				'mail'	=> isset($props[PROPERTY_USER_MAIL])		? (string)array_pop($props[PROPERTY_USER_MAIL])		: '',
				'first'	=> isset($props[PROPERTY_USER_FIRSTNAME])	? (string)array_pop($props[PROPERTY_USER_FIRSTNAME]): '',
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
		$user = new core_kernel_classes_Resource($this->getRequestParameter('userid'));
		$uris = array();
		foreach (core_kernel_users_Service::singleton()->getUserRoles($user) as $role) {
			$uris[] = $role->getUri();
		}
		return $this->returnSuccess(array('roles' => $uris));
	}
	
	public function getRoleUsers() {
		if (!$this->hasRequestParameter('groupid')) {
			throw new common_Exception('Missing paramtere');
		}
		$group = new core_kernel_classes_Class($this->getRequestParameter('groupid'));
		$uris = array();
		foreach ($group->getInstances('true') as $user) {
			$uris[] = $user->getUri();
		}
		return $this->returnSuccess(array('users' => $uris));
	}

}
?>