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
class tao_actions_AuthApi extends tao_actions_RemoteServiceModule {
	
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
				'user'	=> tao_actions_UserApi::buildInfo($user)
			));
		}
	}
}
?>