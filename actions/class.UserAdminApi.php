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
		$taoManager = new core_kernel_classes_Class(INSTANCE_ROLE_TAOMANAGER);
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