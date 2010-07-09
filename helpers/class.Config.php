<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/class.Config.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 09.07.2010, 17:14:22 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000220E-includes begin
// section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000220E-includes end

/* user defined constants */
// section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000220E-constants begin
// section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000220E-constants end

/**
 * Short description of class tao_helpers_Config
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Config
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute MODE_PRODUCTION
     *
     * @access public
     * @var int
     */
    const MODE_PRODUCTION = 1;

    /**
     * Short description of attribute MODE_DEVELOPMENT
     *
     * @access public
     * @var int
     */
    const MODE_DEVELOPMENT = 2;

    /**
     * Short description of attribute MODE_DEBUG
     *
     * @access public
     * @var int
     */
    const MODE_DEBUG = 3;

    /**
     * Short description of attribute mode
     *
     * @access protected
     * @var int
     */
    protected static $mode = 0;

    /**
     * Short description of attribute parameters
     *
     * @access protected
     * @var array
     */
    protected static $parameters = array();

    // --- OPERATIONS ---

    /**
     * Short description of method getMode
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return int
     */
    public static function getMode()
    {
        $returnValue = (int) 0;

        // section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000222E begin
        
        $returnValue = self::$mode;
        
        // section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000222E end

        return (int) $returnValue;
    }

    /**
     * Short description of method setMode
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  int mode
     * @return mixed
     */
    public static function setMode($mode)
    {
        // section 127-0-1-1-6559e665:129b7bc8cb3:-8000:0000000000002230 begin
        
    	self::$mode = $mode;
    	
        // section 127-0-1-1-6559e665:129b7bc8cb3:-8000:0000000000002230 end
    }

    /**
     * Short description of method get
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string key
     * @return mixed
     */
    public static function get($key)
    {
        $returnValue = null;

        // section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000223A begin
        
        if(!empty($key)){
	        if(array_key_exists($key, self::$parameters)){
	        	$returnValue = self::$parameters[$key];
	        }
        }
        
        // section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000223A end

        return $returnValue;
    }

    /**
     * Short description of method getAll
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return array
     */
    public static function getAll()
    {
        $returnValue = array();

        // section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000223D begin
        
        $returnValue = self::$parameters;
        
        // section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000223D end

        return (array) $returnValue;
    }

    /**
     * Short description of method load
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string source
     * @param  boolean extends
     * @return mixed
     */
    public static function load($source, $extends = false)
    {
        // section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000223F begin
        
    	if(!file_exists($source)){
    		throw new Exception("Unable to load configuration file $source");
    	}
    	
    	$functions = array(
    		'_DIRNAME' => dirname($source)
    	);
    	
    	$xml = simplexml_load_file($source, "SimpleXMLElement");
		if($xml instanceof SimpleXMLElement){
			
			//define the mode
			$modeNodes = $xml->xpath('//mode');
			if(is_array($modeNodes)){
				$modeNode = $modeNodes[0];
	    		if($modeNode instanceof SimpleXMLElement){
	    			$mode = strtoupper((string)$modeNode);
	    			switch($mode){
	    				case 'PRODUCTION': 	self::setMode(self::MODE_PRODUCTION); break;
    					case 'DEVELOPMENT':	self::setMode(self::MODE_DEVELOPMENT); break;
    					case 'DEBUG':		self::setMode(self::MODE_DEBUG); break;
	    			}
	    		}
			}
			
			if(!$extends){
				
			}
			
			//parse the parameters
			$paramNodes = $xml->xpath('//param');
			foreach($paramNodes as $paramNode){
				(isset($paramNode['value'])) ? $value = (string)$paramNode['value'] : $value = (string)$paramNode;
				$matches = array();
				if(preg_match_all("/\{_[A-Za-z0-9]*\}/", $value, $matches)  !== false){
					foreach($matches[0] as $match){
						$function = preg_replace("/[\{\}]/",'', $match);
						$value = str_replace($match, $functions[$function], $value);
					}
				}
				
				$matches = array();
				if(preg_match_all("/\{[A-Za-z0-9\_]*\}/", $value, $matches) !== false){
					foreach($matches[0] as $match){
						$param = preg_replace("/[\{\}]/",'', $match);
						$value = str_replace($match, self::get($param), $value);
					}
				}
				self::$parameters[(string)$paramNode['key']] = $value;
			}
		}
		
        // section 127-0-1-1-6559e665:129b7bc8cb3:-8000:000000000000223F end
    }

} /* end of class tao_helpers_Config */

?>