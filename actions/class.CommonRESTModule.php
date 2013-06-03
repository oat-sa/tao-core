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

	const realm = "azeaze";
	private $acceptedMimeTypes = array("application/json", "text/xml", "application/xml");
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
		$this->responseEncoding = (self::acceptHeader($this->acceptedMimeTypes, $this->getHeader("Accept")));
		
		
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
	/*"distribute" actions accroding to REST protocol*/
	public function index(){
	    $uri = null;
	   
	    if ($this->hasRequestParameter("uri")){
		$uri = $this->getRequestParameter("uri");
	    }
	    switch ($this->getRequestMethod()) {
		case "GET":{$this->get($uri);break;}
		case "PUT":{$this->put($uri);break;}
		case "POST":{$this->post($uri);break;}
		case "DELETE":{$this->delete($uri);break;}
	    }
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
		case "auth":{
		    $digest = $this->getDigest();
		    $data = $this->http_digest_parse($digest);
		    //store the hash A1 as a property to be updated on register/changepassword
		    $trialLogin = 'admin'; $trialPassword = 'admin';
		    $A1 = md5($trialLogin . ':' . $this::realm . ':' . $trialPassword);
		    $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
		    $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
		    return (($data['response'] == $valid_response));}
		case "Basic":{
		    if (!(isset($_SERVER['PHP_AUTH_USER']))) return false;
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
	private function getDigest() {
	    //seems apache-php is absorbing the header
	    if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
		$digest = $_SERVER['PHP_AUTH_DIGEST'];
	    // most other servers
	    } elseif (isset($_SERVER['HTTP_AUTHENTICATION'])) {
		    if (strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']),'digest')===0)
		      $digest = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
	    }	else return false;
	    
	    return $digest;
	}
	private function http_digest_parse($digest)
	{
	    // protect against missing data
	    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
	    $data = array();
	    $keys = implode('|', array_keys($needed_parts));

	    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $digest, $matches, PREG_SET_ORDER);

	    foreach ($matches as $m) {
		$data[$m[1]] = $m[3] ? $m[3] : $m[4];
		unset($needed_parts[$m[1]]);
	    }
	    return $needed_parts ? false : $data;
	}
	private function requireLogin(){
	    switch ($this->authMethod){
		case "auth":{
			header('HTTP/1.1 401 Unauthorized');
			header('WWW-Authenticate: Digest realm="'.$this::realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($this::realm).'"');break;}
		case "Basic":{
			header('WWW-Authenticate: Basic realm="My Realm'.$this::realm.'"');
			header('HTTP/1.0 401 Unauthorized');break;}
	    }
	}
	protected function encode($data){
	    print_r($data);
	    switch ($this->responseEncoding){
		case "XMLRDF":{}
		case "text/xml":{}
		case "application/xml":{echo tao_helpers_xml::from_array($data);break;}
		case "application/json":{echo json_encode($data);}
		default:{echo json_encode($data);}
	    }
	}
	public static function	acceptHeader($supportedMimeTypes = null, $requestedMimeTypes = null) {
	    $acceptTypes = Array ();
	    $accept = strtolower($requestedMimeTypes);
	    $accept = explode(',', $accept);
	    foreach ($accept as $a) {
		// the default quality is 1.
		$q = 1;
		// check if there is a different quality
		if (strpos($a, ';q=')) {
		    // divide "mime/type;q=X" into two parts: "mime/type" i "X"
		    list($a, $q) = explode(';q=', $a);
		}
		// mime-type $a is accepted with the quality $q
		// WARNING: $q == 0 means, that mime-type isn’t supported!
		$acceptTypes[$a] = $q;
	    }
	    arsort($acceptTypes);
	    if (!$supportedMimeTypes) return $AcceptTypes;
	    $supportedMimeTypes = array_map('strtolower', (array)$supportedMimeTypes);
	    // let’s check our supported types:
	    foreach ($acceptTypes as $mime => $q) {
	    if ($q && in_array(trim($mime), $supportedMimeTypes)) return trim($mime);
	    }
	    // no mime-type found
	    return null;
	}
}
?>