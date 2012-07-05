<?php
/**
 * This controller provide the actions to manage the application users (list/add/edit/delete)
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_AuthService extends tao_actions_CommonModule {
	
	const ALLOWED_ROLE = CLASS_ROLE_WORKFLOWUSERROLE;

	public function login() {
		if (!$this->hasRequestParameter('username') || !$this->hasRequestParameter('password')) {
			throw new common_Exception('Missing paramteres');
		}
		$userService = core_kernel_users_Service::singleton();
		$user = $userService->getOneUser($this->getRequestParameter('username'), new core_kernel_classes_Class(self::ALLOWED_ROLE));
		if (!is_null($user)) {
			$loggedIn = $userService->isPasswordValid($this->getRequestParameter('password'));
		} else {
			$loggedIn = false;
		}
		
		if ($correct) {
			return json_encode(array(
				'success'	=> true,
				'id'		=> 'joel.bout',
				'token'		=> md5('code')
			));
		} else {
			return json_encode(array(
				'success'	=> false,
				'message'	=> __('Login failed')
			));
		}
	}
}
?>