<?php

error_reporting(E_ALL);

/**
 * Utilities on requests
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A23-includes begin
// section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A23-includes end

/* user defined constants */
// section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A23-constants begin
// section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A23-constants end

/**
 * Utilities on requests
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Request
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Enables you to know if the request in the current scope is an ajax
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return boolean
     */
    public static function isAjax()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A24 begin
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
			if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
				$returnValue = true;
			}
		}
        // section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A24 end

        return (bool) $returnValue;
    }

    /**
     * Perform an HTTP Request on the defined url and return the content
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string url
     * @param  boolean useSession if you want to use the same session in the remotre server
     * @return string
     */
    public static function load($url, $useSession = false)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-673d6215:12afb9a0b0f:-8000:00000000000025A1 begin
        
        if(!empty($url)){
	        if($useSession){
	   			session_write_close();
	        }
			
	        $curlHandler = curl_init();
			
	        //if there is an http auth, it's mandatory to connect with curl
			if(USE_HTTP_AUTH){
				curl_setopt($curlHandler, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	            curl_setopt($curlHandler, CURLOPT_USERPWD, USE_HTTP_USER.":".USE_HTTP_PASS);
			}
			curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
			
			//to keep the session
	        if($useSession){
				if(!preg_match("/&$/", $url)){
					$url .= '&';
				}
				$url .= 'session_id=' . session_id();
				curl_setopt($curlHandler, CURLOPT_COOKIE, session_name(). '=' . $_COOKIE[session_name()] . '; path=/'); 
	        }
	        
			curl_setopt($curlHandler, CURLOPT_URL, $url);
				
			$returnValue = curl_exec($curlHandler);
			if(curl_errno($curlHandler) > 0){
				throw new Exception("Request error ".curl_errno($curlHandler).": ".  curl_error($curlHandler));
			}
			curl_close($curlHandler);  
        }
        
        // section 127-0-1-1-673d6215:12afb9a0b0f:-8000:00000000000025A1 end

        return (string) $returnValue;
    }

    /**
     * Check if a value is contained in the current request.
     * The value can be found in one the follwing types: extension, module,
     * paramKey, paramValue
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string type
     * @param  string value
     * @return boolean
     */
    public static function contains($type, $value)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--11252040:12dc6d37693:-8000:0000000000002CE6 begin
        
        $resolver = new Resolver();
		$action	= $resolver->getAction();
    	$module	= $resolver->getModule();
        
        switch($type){
        	case 'extension': 
        		//get the token before the module
        		if(strpos('?', $_SERVER['REQUEST_URI']) > -1){
        			$baseUrl = substr($_SERVER['REQUEST_URI'], 0, strpos('?', $_SERVER['REQUEST_URI']));
        			$tokens = explode('/', $baseUrl);
        		}
        		else{
        			$tokens = explode('/', $_SERVER['REQUEST_URI']);
        		}
        		$extension = '';
        		foreach($tokens as $index => $token){
        			if($token == $module){
        				if(isset($tokens[$index - 1])){
        					$extension = $tokens[$index - 1];
        					break;
        				}
        			}
        		}
        		if(!empty($extension)){
        			if(trim($extension) == trim($value)){
        				$returnValue = true;
        			}
        		}
        		break;
        	case 'module': 
        		if(!empty($module)){
        			if(trim($module) == trim($value)){
        				$returnValue = true;
        			}
        		}
        		break;
        	case 'action': 
        		if(!empty($action)){
        			if(trim($action) == trim($value)){
        				$returnValue = true;
        			}
        		}
        		break;
        	case 'paramKey': 
        	case 'paramValue': 
        		$tokens = explode('&', $_SERVER['QUERY_STRING']);
        		foreach($tokens as $token){
        			$paramTokens = explode('=', $token);
        			if($type == 'paramKey' && isset($paramTokens[0])){
        				if(trim($paramTokens[0]) == trim($value)){
        					$returnValue = true;
        					break;
        				}
        			}
        			if($type == 'paramValue' && isset($paramTokens[1])){
        				if(trim($paramTokens[1]) == trim($value)){
        					$returnValue = true;
        					break;
        				}
        			}
        		}
        		break;
        }
        
        // section 127-0-1-1--11252040:12dc6d37693:-8000:0000000000002CE6 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_Request */

?>