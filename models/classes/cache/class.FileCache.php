<?php

error_reporting(E_ALL);

/**
 * TAO - tao/models/classes/cache/class.FileCache.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 21.08.2012, 11:34:15 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes_cache
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * basic interface a cache implementation has to implement
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/models/classes/cache/interface.Cache.php');

/**
 * Service is the base class of all services, and implements the singleton
 * for derived services
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/models/classes/class.Service.php');

/* user defined includes */
// section 127-0-1-1-98ccd32:136108f4ede:-8000:000000000000388A-includes begin
// section 127-0-1-1-98ccd32:136108f4ede:-8000:000000000000388A-includes end

/* user defined constants */
// section 127-0-1-1-98ccd32:136108f4ede:-8000:000000000000388A-constants begin
// section 127-0-1-1-98ccd32:136108f4ede:-8000:000000000000388A-constants end

/**
 * Short description of class tao_models_classes_cache_FileCache
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes_cache
 */
class tao_models_classes_cache_FileCache
    extends tao_models_classes_Service
        implements tao_models_classes_cache_Cache
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * puts "something" into the cache,
     * If this is an object and implements Serializable,
     * we use the serial provided by the object
     * else a serial must be provided
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  mixed
     * @param  string serial
     * @return mixed
     */
    public function put($mixed, $serial = null)
    {
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003703 begin
        if ($mixed instanceof tao_models_classes_Serializable) {
        	if (!is_null($serial) && $serial != $mixed->getSerial()) {
        		throw new common_exception_Error('Serial mismatch for Serializable '.$mixed->getSerial());
        	}
        	$serial = $mixed->getSerial();
        }
		$handle = fopen($this->getFilePath($serial), 'w');
		fwrite($handle, "<? return ".$this->buildPHPVariableString($mixed).";?>");
		fclose($handle);
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003703 end
    }

    /**
     * gets the entry associted to the serial
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string serial
     * @return tao_models_classes_Serializable
     */
    public function get($serial)
    {
        $returnValue = null;

        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003706 begin
        if ($this->has($serial)) {
	        try {
	        	$returnValue = include $this->getFilePath($serial);
	        } catch (Exception $e) {
	        	common_Logger::d('Exception while reading cache entry for '.$serial);
	        	
	        }
        } else {
        	throw new tao_models_classes_cache_NotFoundException('Failed to get ('.$serial.') from filecache');
        }
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003706 end

        return $returnValue;
    }

    /**
     * test whenever an entry associted to the serial exists
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string serial
     * @return boolean
     */
    public function has($serial)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-3618cc42:139487c1385:-8000:0000000000003B8C begin
        $returnValue = file_exists($this->getFilePath($serial));
        // section 127-0-1-1-3618cc42:139487c1385:-8000:0000000000003B8C end

        return (bool) $returnValue;
    }

    /**
     * removes an entry from the cache
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string serial
     * @return mixed
     */
    public function remove($serial)
    {
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003700 begin
        @unlink($this->getFilePath($serial));
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003700 end
    }

    /**
     * empties the cache
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function purge()
    {
        // section 127-0-1-1--18485ef3:13542665222:-8000:00000000000065B1 begin
        if (false !== ($files = scandir(CACHE_PATH))){
            foreach ($files as $f) {
                $filePath = CACHE_PATH . $f;
                if (substr($f, 0, 1) != '.' && file_exists($filePath)){
                    @unlink($filePath);
                }
            }
        }
        
        // section 127-0-1-1--18485ef3:13542665222:-8000:00000000000065B1 end
    }

    /**
     * Short description of method buildPHPVariableString
     *
     * @access private
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  mixed
     * @return string
     */
    private function buildPHPVariableString($mixed)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-70417e84:13615be5f59:-8000:00000000000068CD begin
        switch (gettype($mixed)) {
        	case "string" :
        		// replace \ by \\ and then ' by \'
        		$returnValue =  '\''.str_replace('\'', '\\\'', str_replace('\\', '\\\\', $mixed)).'\'';
        		break;
        	case "boolean" :
        		$returnValue = $mixed ? 'true' : 'false';
        		break;
        	case "integer" :
        	case "double" :
        		$returnValue = $mixed;
        		break;
        	case "array" :
				$string = "";
				foreach ($mixed as $key => $val) {
					$string .= $this->buildPHPVariableString($key)." => ".$this->buildPHPVariableString($val).",";
				}
				$returnValue = "array(".substr($string, 0, -1).")";
				break;
        	case "null" :
        		$returnValue = null;
				break;
        	case "object" :
        		$returnValue = 'unserialize(\''.serialize($mixed).'\')';
        		break;
        	default:
    			// ressource and unexpected types
        		common_Logger:w("Could not store variable of type ".gettype($mixed)." in ".get_called_class());
        }
        // section 127-0-1-1-70417e84:13615be5f59:-8000:00000000000068CD end

        return (string) $returnValue;
    }

    /**
     * Short description of method getFilePath
     *
     * @access private
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string serial
     * @return string
     */
    private function getFilePath($serial)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-70417e84:13615be5f59:-8000:00000000000068D0 begin
        $returnValue = common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->getConstant('CACHE_PATH').$serial;
        // section 127-0-1-1-70417e84:13615be5f59:-8000:00000000000068D0 end

        return (string) $returnValue;
    }

} /* end of class tao_models_classes_cache_FileCache */

?>