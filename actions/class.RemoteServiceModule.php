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
class tao_actions_RemoteServiceModule extends Module {
	
	const SESSION_DURATION = 43200; // 12 horus
	
	private $currentUser = null;

	/**
	 * constructor checks if a user is logged in
	 * If you don't want this check, please override the  _isAllowed method to return true
	 */
	public function __construct()
	{
		if(!$this->_isAllowed()){
			throw new tao_models_classes_UserException(__('Access denied. Please renew your authentication!'));
		}
	}
	
	/**
	 * Allows a remote system to connect a tao User
	 */
	public function login() {
		
		$user = $this->doLogin();
		
		if ($user == false) {
			echo json_encode(array(
				'success'	=> false,
				'message'	=> __('Login failed')
			));
		} else {
			echo json_encode(array(
				'success'	=> true,
				'user'		=> $user->getUri(),
				'token'		=> $this->buildToken($user)
			));
		}
	}
	
	/**
	 * Searches for the user with the provided username and verifies his password 
	 * 
	 * @throws common_Exception
	 * @return core_kernel_classes_Resource the logged in user or null
	 */
	protected function doLogin() {
		if (!$this->hasRequestParameter('username') || !$this->hasRequestParameter('password')) {
			throw new common_Exception('Missing paramteres');
		}
		$userService = core_kernel_users_Service::singleton();
		$user = $userService->getOneUser($this->getRequestParameter('username'));
		if (is_null($user)) {
			return false;
		}
		if ($userService->isPasswordValid($this->getRequestParameter('password'), $user)) {
			$this->currentUser = $user;
			return $user;
		} else {
			return false;
		}
	}
	
	protected function returnFailure($errormsg = '') {
		echo json_encode(array(
			'success'	=> false,
			'error'		=> $errormsg
		));
		
	}
	
	protected function returnSuccess($data = array()) {
		$data['success']	= true;
		$data['token']		= $this->buildToken($this->getCurrentUser());	
		echo json_encode($data);
	}
	
	/**
	 * This function should build an authentification token for the user
	 * This function is NOT yet secure
	 * 
	 * @param core_kernel_classes_Resource $user
	 * @return returns a token string
	 */
	protected function buildToken(core_kernel_classes_Resource $user, $time = null) {
		$time = is_null($time) ? time() : $time;
		$userPass = (string)$user->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_PASSWORD));
		return $time.'_'.md5($time.$user->getUri().$userPass);
	}
	
	/**
	 * Returns the current user or null
	 * @todo verify first
	 * 
	 * @return core_kernel_classes_Resource
	 */
	protected function getCurrentUser() {
		if ($this->currentUser == null) {
			if ($this->hasRequestParameter('user')) {
				$userUri			= $this->getRequestParameter('user');
				$this->currentUser	= new core_kernel_classes_Resource($userUri);
			}
		}
		return $this->currentUser;
	}
	
	private function getUserFromToken() {
		if (!$this->hasRequestParameter('token') || !$this->hasRequestParameter('user')) {
			return null;
		}
		$token		= $this->getRequestParameter('token');
		$userUri	= $this->getRequestParameter('user');
		$user		= new core_kernel_classes_Resource($userUri);
		
		$time = substr($token, 0, strpos($token, '_'));
		if (!is_numeric($time) || time() - $time > self::SESSION_DURATION) {
			common_Logger::i('Session timed out '.$time.' for user '.$userUri);
			return null;
		}
		
		if ($token != $this->buildToken($user, $time)) {
			common_Logger::w('Invalid token for user '.$userUri);
			return null;
		}
		common_Logger::d('User '.$user->getUri().' authentificated via token.');
		return $user;
	}
	
	protected function _isAllowed() {
		
		$user = $this->getUserFromToken();
		
		$context = Context::getInstance();
		$ext	= $context->getExtensionName();
		$module = $context->getModuleName();
		$action = $context->getActionName();
		
		// @todo link to ACL
		//return tao_helpers_funcACL_funcACL::hasAccess($ext, $module, $action);
		return (!is_null($user) || $action == 'login');
	}
}
?>