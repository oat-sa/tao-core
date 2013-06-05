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
 * 
 *
 * @author patrick implements the restcontroller module type with an HTTP digest login/Basic protocol
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 */
abstract class tao_actions_CommonRESTModule extends tao_actions_CommonModule {

	const realm = "TAO rest";
	private $acceptedMimeTypes = array("application/json", "text/xml", "application/xml", "application/rdf+xml");
	private $authMethod = "Basic"; //{auth, Basic}
	private $responseEncoding = "application/json";  //{application/json, text/xml, application/xml}
	private $currentUser = null;
	private $headers = null;

	abstract public function get($uri);
	abstract public function put($uri);
	abstract public function post($uri);
	abstract public function delete($uri);

	public function __construct(){
	    parent::__construct();
	    //$this->headers = HttpResponse::getRequestHeaders();
	    $this->headers = apache_request_headers();
	    if ($this->hasHeader("Accept")){
		$this->responseEncoding = (tao_helpers_Http::acceptHeader($this->acceptedMimeTypes, $this->getHeader("Accept")));
	    }
	}
	/*override to add header parameters*/
	public function hasRequestParameter($string){
	    return parent::hasRequestParameter() || isset($this->headers[$string]);
	}
	public function getRequestParameter($string){
	    if (isset($this->headers[$string])) return ($this->headers[$string]);
	   //if (parent::hasRequestParameter())
		return parent::getRequestParameter();

	}
	public function getHeader($string){
	     if (isset($this->headers[$string])) return ($this->headers[$string]); else return false;
	}
	public function hasHeader($string){
	     if (isset($this->headers[$string])) return true; else return false;
	}
	/*redistribute actions*/
	public function index(){
	    $uri = null;
	    if ($this->hasRequestParameter("uri")){
		$uri = $this->getRequestParameter("uri");
		if (!(common_Utils::isUri($uri))) {$this->returnFailure(1, "Not a valid uri");}
	    }
	   
	    switch ($this->getRequestMethod()) {
		case "GET":{$this->get($uri);break;}
		case "PUT":{$this->put($uri);break;}
		case "POST":{$this->post($uri);break;}
		case "DELETE":{$this->delete($uri);break;}
	    }
	}
	/* commodity as Http-auth (like the rest of the HTTP spec) is meant to be stateless
	 * As per RFC2616 "Existing HTTP clients and user agents typically retain authentication information indefinitely. "
	 * " is a question of getting the browser to forget the credential information, so that the next time the resource is requested, the username and password must be supplied again"
	 * "you can't. Sorry."
	 * Workaround used here for web browsers: provide an action taht sends a 401 and get the the web browsers to log in again
	 * Programmatic agents should send credentials directly
	 */
	public function logout(){
	    
	    $this->requireLogin();
	}
	public function _isAllowed(){
	    //die("azeazeaze");
		 if (!($this->isValidLogin())) {$this->requireLogin();die();}
		$context = Context::getInstance();
		$ext	= $context->getExtensionName();
		$module = $context->getModuleName();
		switch ($this->getRequestMethod()) {
		case "GET":{$action = "get";break;}
		case "PUT":{$action = "put";;break;}
		case "POST":{$action = "post";break;}
		case "DELETE":{$action = "delete";;break;}
		}
		//echo $ext; echo $module; echo $context->getActionName();die();
		//not yet working in this context
		//return tao_helpers_funcACL_funcACL::hasAccess($ext, $module, $action);
		return true;
	}
	private function isValidLogin(){
	    $returnValue = false;
	    $userService = tao_models_classes_UserService::singleton();
	    switch ($this->authMethod){
		//"Because of the way that Basic authentication is specified, your username and password must be verified every time you request a document from the server"
		case "auth":{ // not yet working
		    $digest = tao_helpers_Http::getDigest();
		    $data = tao_helpers_Http::parseDigest($digest);
		    //store the hash A1 as a property to be updated on register/changepassword
		    $trialLogin = 'admin'; $trialPassword = 'admin';
		    $A1 = md5($trialLogin . ':' . $this::realm . ':' . $trialPassword);
		    $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
		    $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
		    return (($data['response'] == $valid_response));}
		case "Basic":{
		    if (!(isset($_SERVER['PHP_AUTH_USER'])) or ($_SERVER['PHP_AUTH_USER']=="")) return false;
		    $userService = tao_models_classes_UserService::singleton();
		    $user = $userService->getOneUser($_SERVER['PHP_AUTH_USER']);
		    if (is_null($user)) {return false;}
		    if ($userService->isPasswordValid($_SERVER['PHP_AUTH_PW'], $user)) {
			$this->currentUser = $user;
			return $user;
		    } else {
			common_Logger::w('API login failed for user '.$_SERVER['PHP_AUTH_USER']);
			return false;
		}
		}
	    }
	}
	private function requireLogin(){
	    switch ($this->authMethod){
		case "auth":{
			header('HTTP/1.1 401 Unauthorized');
			header('WWW-Authenticate: Digest realm="'.$this::realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($this::realm).'"');break;}
		case "Basic":{
			header('WWW-Authenticate: Basic realm="'.$this::realm.'"');
			header('HTTP/1.0 401 Unauthorized');break;}
	    }
	}
	/**
	 * returnSuccess and returnFailure should be used
	 */
	private function encode($data){
	switch ($this->responseEncoding){
		case "XMLRDF":{}
		case "text/xml":{}
		case "application/xml":{return tao_helpers_Xml::from_array($data);break;}
		case "application/json":{return json_encode($data);}
		default:{return json_encode($data);}
	    }
	}
	protected function returnFailure($errorCode = 500, $errorMsg = '') {
	    $data = array();
	    $data['success']	=  false;
	    $data['errorCode']	=  $errorCode;
	    $data['errorMsg']	=  $errorMsg;
	    $data['version']	= TAO_VERSION;
	    echo $this->encode($data);
	    exit(0);
	}
	protected function returnSuccess($rawData = array()) {
	     $data = array();
	    $data['success']	= true;
	    $data['data']	= $rawData;
	    $data['version']	= TAO_VERSION;
	    echo $this->encode($data);
	    exit(0);
	}
}
?>