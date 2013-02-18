<?php

//require_once(GENERIS_BASE_PATH."/includes/oauth-php/library/OAuthStore.php");
//require_once(GENERIS_BASE_PATH."/includes/oauth-php/library/OAuthServer.php");
//require_once(GENERIS_BASE_PATH."/includes/oauth-php/library/OAuthRequestVerifier.php");

/**
 * This controller provides a service to allow other sites to authenticate against
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_OAuthApi extends Module {
	
	const SESSION_DURATION = 43200; // 12 horus

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
	
	public function request_token() {
		$this->logHeader();
		
		$store = $this->initStore();
		$server = new OAuthServer();
		
		// this dumps the request info to stdOut
		$token = $server->requestToken();
		
		common_Logger::i('token '.$token);
	}
	
	public function authorize() {
		
		$user = $this->doLogin();
		
		common_Logger::d('Login: '.$user);
		
		$this->logHeader();
		$store = $this->initStore();
		$server = new OAuthServer();
		$server->authorizeVerify();
			
		$server->authorizeFinish(true, 12345);
		
		$store->exchangeConsumerRequestForAccessToken($this->getRequestParameter('oauth_token'), array());		
		return $this->returnSuccess();
		//$server->accessToken();
/*	
		$store = $this->initStore();
		$server = new OAuthServer();
		
		//$user = $this->doLogin();
		common_Logger::d('user '.$user);
		if ($user !== false) {
			$server->authorizeVerify();
			// auto allow
			$server->authorizeFinish(true, 12345);
			return $this->returnSuccess();
		} else {
			return $this->returnFailure('no user');
		}
	*/
		echo 'link: <a href="'.$this->getRequestParameter('oauth_callback').'">callback</a>';	
	}
	
	public function test() {
		$store = $this->initStore();
		$this->logHeader();
		foreach ($_POST as $k => $v) {
			common_Logger::d($k.' = '.$v);
		}		
		if (OAuthRequestVerifier::requestIsSigned()) {
			common_Logger::i('Is Signed');
			$req = new OAuthRequestVerifier();
			$user_id = $req->verify();
			common_Logger::i('Got User \''.$user_id.'\'');
			$user = new core_kernel_classes_Resource($user_id);
			$this->returnSuccess(array(	'user'	=> tao_actions_UserApi::buildInfo($user)));
		}
	}
	
	public function access_token() {
		$this->logHeader();
		$this->initStore();
		
		$server = new OAuthServer();
		$server->accessToken();
		/*
		if (OAuthRequestVerifier::requestIsSigned()) {
			common_Logger::i('Is Signed');
			$req = new OAuthRequestVerifier();
            $user_id = $req->verify();
			common_Logger::i('Got User '.$user_id);
		}
		*/
		return $this->returnFailure(__('Not yet implemented'));
	}
	
	private function initStore() {
		return OAuthStore::instance("MySQL", array(
		    'server'	=> DATABASE_URL,
		    'username'	=> DATABASE_LOGIN,
			'password'	=> DATABASE_PASS,
			'database'	=> 'oauth'
		));
	}
	
	private function logHeader() {
		$headers = apache_request_headers();
		if (isset($headers['Authorization'])) {
			$auth = $headers['Authorization'];
			common_Logger::d($auth);
		} else {
			common_Logger::d('no Authorization header found');
		}
	}
	
	private function registerConsumer() {
		$consumer = array(
		    // These two are required
		    'requester_name' => 'John Doe',
		    'requester_email' => 'john@example.com',
		);
		$store = OAuthStore::instance();
		$user = core_kernel_classes_Session::singleton()->getUserUri();
		$key   = $store->updateConsumer($consumer, $user);
		common_Logger::i('got Consumer '.$key );
		
		// Get the complete consumer from the store
		$consumer = $store->getConsumer($key);
		foreach ($consumer as $k => $v) {
			common_Logger::i($k.' = '.$v);
		}
	}
	
	protected function _isAllowed() {
		return true;
	}
	
	protected function getUserFromToken() {
		$returnValue = null;
		
		$store = $this->initStore();
		if (OAuthRequestVerifier::requestIsSigned()) {
			common_Logger::i('Is Signed');
			$req = new OAuthRequestVerifier();
			$user_id = $req->verify();
			common_Logger::i('Got User \''.$user_id.'\'');
			$returnValue = new core_kernel_classes_Resource($user_id);
		}
		
		return $returnValue;
	}
	
		
	protected function returnFailure($errormsg = '') {
		echo json_encode(array(
			'success'	=> false,
			'error'		=> $errormsg
		));
		
	}
	
	protected function returnSuccess($data = array()) {
		$data['success']	= true;
		/*if (!is_null($this->getCurrentUser())) {
			$data['token']		= $this->buildToken($this->getCurrentUser());
		}
		*/	
		echo json_encode($data);
	}
	
	
}
?>