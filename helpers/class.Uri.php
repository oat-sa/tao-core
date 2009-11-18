<?php

error_reporting(E_ALL);

/**
 * Utilities on URL/URI
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019D2-includes begin
// section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019D2-includes end

/* user defined constants */
// section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019D2-constants begin

/**
 * Conveniance function 
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @param  string action
 * @param  string module
 * @param  array params
 * @return 
 */
function _url($action = null, $module = null, $params = array()){
	return tao_helpers_Uri::url($action, $module, $params);
}

// section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019D2-constants end

/**
 * Utilities on URL/URI
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Uri
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * the base url
     *
     * @access private
     * @var mixed
     */
    private static $base = null;

    // --- OPERATIONS ---

    /**
     * get the project base url
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public static function getBaseUrl()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A45 begin
		if(is_null(self::$base) && defined('BASE_URL')){
			self::$base = BASE_URL;
		}
		$returnValue = self::$base;
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A45 end

        return (string) $returnValue;
    }

    /**
     * conveniance method to create urls based on the current MVC context and
     * it for the used kind of url resolving
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string action
     * @param  string module
     * @param  array params
     * @return string
     */
    public static function url($action = null, $module = null, $params = array())
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A26 begin
		$returnValue = self::getBaseUrl();
		
		$context = Context::getInstance();
		if(is_null($module)){
			$module = $context->getModuleName();
		}
		if(is_null($action)){
			$action = $context->getActionName();
		}
		$returnValue .= '/' . $module . '/' . $action;
		if(count($params) > 0){
			$returnValue .= '?';
			if(is_string($params)){
				$returnValue .= $params;
			}
			if(is_array($params)){
				foreach($params as $key => $value){
					$returnValue .= $key . '=' . urlencode($value) . '&';
				}
			}
		}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A26 end

        return (string) $returnValue;
    }

    /**
     * encode an URI
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string uri
     * @param  boolean dotMode
     * @return string
     */
    public static function encode($uri, $dotMode = true)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A3F begin
		
		if( preg_match("/^http/", $uri)){
			if($dotMode){
				$returnValue = urlencode(
					str_replace('.', '__', $uri)
				);
			}
			else{
				$returnValue = urlencode($uri);
			}
		}
		else{
			$returnValue = $uri;
		}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A3F end

        return (string) $returnValue;
    }

    /**
     * decode an URI
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string uri
     * @param  boolean dotMode
     * @return string
     */
    public static function decode($uri, $dotMode = true)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A42 begin
		if( preg_match("/^http/", $uri)){
			if($dotMode){
				$returnValue = urldecode(
					str_replace('__', '.', $uri)
				);
			}
			else{
				$returnValue = urldecode($uri);
			}
		}
		else{
			$returnValue = $uri;
		}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A42 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_Uri */

?>