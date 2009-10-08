<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/class.Uri.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 07.10.2009, 11:33:28 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
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
 * @param mixed $params [optional]
 * @param mixed $action [optional]
 * @param mixed $module [optional]
 * @return 
 */
function _url( $params = null, $action = null, $module = null){
	return tao_helpers_Uri::url($params, $action, $module);
}

// section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019D2-constants end

/**
 * Short description of class tao_helpers_Uri
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
     * Short description of attribute base
     *
     * @access private
     * @var mixed
     */
    private static $base = null;

    // --- OPERATIONS ---

    /**
     * Short description of method getBaseUrl
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
     * Short description of method url
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  mixed params
     * @param  mixed action
     * @param  mixed module
     * @return string
     */
    public static function url($params = null, $action = null, $module = null)
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
		if(!is_null($params)){
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
     * Short description of method encode
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string uri
     * @return string
     */
    public static function encode($uri)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A3F begin
		if(preg_match("/^http/", $uri)){
			$returnValue = urlencode(
				str_replace('.', '_', $uri)
			);
		}
		else{
			$returnValue = $uri;
		}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A3F end

        return (string) $returnValue;
    }

    /**
     * Short description of method decode
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string uri
     * @return string
     */
    public static function decode($uri)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A42 begin
		if(preg_match("/^http/", $uri)){
			$returnValue = urldecode(
				str_replace('_', '.', $uri)
			);
		}
		else{
			$returnValue = $uri;
		}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A42 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_Uri */

?>