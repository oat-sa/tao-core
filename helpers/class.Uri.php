<?php

error_reporting(E_ALL);

/**
 * Utilities on URL/URI
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
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
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @param  string action
 * @param  string module
 * @param  string extension
 * @param  array params
 * @return
 */
function _url($action = null, $module = null, $extension = null, $params = array()){
	return tao_helpers_Uri::url($action, $module, $extension, $params);
}

// section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019D2-constants end

/**
 * Utilities on URL/URI
 *
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
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

    /**
     * Short description of attribute root
     *
     * @access private
     * @var mixed
     */
    private static $root = null;

    /**
     * Short description of attribute ENCODE_ARRAY_KEYS
     *
     * @access public
     * @var int
     */
    const ENCODE_ARRAY_KEYS = 1;

    /**
     * Short description of attribute ENCODE_ARRAY_VALUES
     *
     * @access public
     * @var int
     */
    const ENCODE_ARRAY_VALUES = 2;

    /**
     * Short description of attribute ENCODE_ARRAY_ALL
     *
     * @access public
     * @var int
     */
    const ENCODE_ARRAY_ALL = 3;

    // --- OPERATIONS ---

    /**
     * get the project base url
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return string
     */
    public static function getBaseUrl()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A45 begin

		if(is_null(self::$base) && defined('BASE_URL')){
			self::$base = BASE_URL;
			if(!preg_match("/\/$/", self::$base)){
				self::$base .= '/';
			}
		}
		$returnValue = self::$base;

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A45 end

        return (string) $returnValue;
    }

    /**
     * Short description of method getRootUrl
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return string
     */
    public static function getRootUrl()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-269c0444:12849d750d4:-8000:0000000000002440 begin

        if(is_null(self::$root) && defined('ROOT_URL')){
			self::$root = ROOT_URL;
			if(!preg_match("/\/$/", self::$root)){
				self::$root .= '/';
			}
		}
		$returnValue = self::$root;

        // section 127-0-1-1-269c0444:12849d750d4:-8000:0000000000002440 end

        return (string) $returnValue;
    }

    /**
     * conveniance method to create urls based on the current MVC context and
     * it for the used kind of url resolving
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string action
     * @param  string module
     * @param  string extension
     * @param  array params
     * @return string
     */
    public static function url($action = null, $module = null, $extension = null, $params = array())
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A26 begin

		$context = Context::getInstance();
		if(is_null($module)){
			$module = $context->getModuleName();
		}
		if(is_null($action)){
			$action = $context->getActionName();
		}

    	if(is_null($extension) || empty($extension)){
			$returnValue = self::getBaseUrl() . $module . '/' . $action;
		}
		else{
			$returnValue = self::getRootUrl(). $extension . '/'. $module . '/' . $action;
		}

		if(count($params) > 0){
			$returnValue .= '?';
			if(is_string($params)){
				$returnValue .= $params;
			}
			if(is_array($params)){
				foreach($params as $key => $value){
					$returnValue .= $key . '=' . urlencode($value) . '&';
				}
				$returnValue = substr($returnValue, 0, -1);
			}
		}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A26 end

        return (string) $returnValue;
    }

    /**
     * format propertly an ol style url
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string url
     * @param  array params
     * @return string
     */
    public static function legacyUrl($url, $params = array())
    {
        $returnValue = (string) '';

        // section 127-0-1-1--399a8411:1284971f0c8:-8000:0000000000002436 begin
        // section 127-0-1-1--399a8411:1284971f0c8:-8000:0000000000002436 end

        return (string) $returnValue;
    }

    /**
     * encode an URI
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string uri
     * @param  boolean dotMode
     * @return string
     */
    public static function encode($uri, $dotMode = true)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A3F begin
		if (preg_match("/^http/", $uri)) {
			if ($dotMode) {
				//$returnValue = urlencode(str_replace('.', '__', $uri));
				$returnValue = str_replace('.', '_0_', str_replace('/', '_1_', str_replace('://', '_2_', str_replace('#', '_3_', $uri))));
			} else {
				$returnValue = str_replace('/', '_1_', str_replace('://', '_2_', str_replace('#', '_3_', $uri)));
			}
		} else {
			$returnValue = $uri;
		}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A3F end

        return (string) $returnValue;
    }

    /**
     * decode an URI
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string uri
     * @param  boolean dotMode
     * @return string
     */
    public static function decode($uri, $dotMode = true)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A42 begin
		if (preg_match("/^http/", $uri)) {
			if ($dotMode) {
				//$returnValue = urldecode(str_replace('__', '.', $uri));
				//$returnValue = str_replace('w_org', 'w3.org', $returnValue);
				$returnValue = str_replace('_3_', '#', str_replace('_2_', '://', str_replace('_1_', '/', str_replace('_0_', '.', $uri))));
			} else {
				$returnValue = str_replace('_3_', '#', str_replace('_2_', '://', str_replace('_1_', '/', $uri)));
			}
		} else {
			$returnValue = $uri;
		}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A42 end

        return (string) $returnValue;
    }

    /**
     * Encode the uris composing either the keys or the values of the array in
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  array uris
     * @param  int encodeMode
     * @param  boolean dotMode
     * @param  boolean uniqueMode
     * @return array
     */
    public static function encodeArray($uris, $encodeMode = 3, $dotMode = true, $uniqueMode = false)
    {
        $returnValue = array();

        // section 127-0-1-1--399a8411:1284971f0c8:-8000:000000000000242B begin

        if(is_array($uris)){
        	foreach($uris as $key => $value){
        		if($encodeMode == self::ENCODE_ARRAY_KEYS || $encodeMode == self::ENCODE_ARRAY_ALL){
        			$key = self::encode($key, $dotMode);
        		}
        		if($encodeMode == self::ENCODE_ARRAY_VALUES || $encodeMode == self::ENCODE_ARRAY_ALL){
        			$value = self::encode($value, $dotMode);
        		}
        		$returnValue[$key] = $value;
        	}
        }

        if($uniqueMode){
        	$returnValue = array_unique($returnValue);
        }

        // section 127-0-1-1--399a8411:1284971f0c8:-8000:000000000000242B end

        return (array) $returnValue;
    }

    /**
     * Short description of method getUniqueId
     *
     * @access public
     * @author somsack.sipasseuth@tudor.lu
     * @param  string uriResource
     * @return string
     */
    public static function getUniqueId($uriResource)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-2045fd08:128b9eb9c51:-8000:0000000000001F71 begin

		//TODO check format of the uri, preg_match()
		if(stripos($uriResource,".rdf#")>0){
			$returnValue = substr($uriResource,stripos($uriResource,".rdf#")+5);
		}

        // section 127-0-1-1-2045fd08:128b9eb9c51:-8000:0000000000001F71 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_Uri */

?>