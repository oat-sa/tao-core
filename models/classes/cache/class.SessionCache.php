<?php

error_reporting(E_ALL);

/**
 * TAO - tao/models/classes/cache/class.SessionCache.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 05.04.2012, 15:45:14 with ArgoUML PHP module 
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
 * include tao_models_classes_cache_Cache
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
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036DE-includes begin
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036DE-includes end

/* user defined constants */
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036DE-constants begin
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036DE-constants end

/**
 * Short description of class tao_models_classes_cache_SessionCache
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes_cache
 */
class tao_models_classes_cache_SessionCache
    extends tao_models_classes_Service
        implements tao_models_classes_cache_Cache
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute items
     *
     * @access public
     * @var array
     */
    public $items = array();

    /**
     * Short description of attribute SESSION_KEY
     *
     * @access public
     * @var string
     */
    const SESSION_KEY = 'cache';

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
        $this->items[$serial] = $mixed;
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
        if (!isset($this->items[$serial])) {
        	if (Session::hasAttribute(static::SESSION_KEY)) {
	        	$storage = Session::getAttribute(static::SESSION_KEY);
		        if(isset($storage[$serial])){
	
		        	$data = @unserialize($storage[$serial]);
		        
		        	// check if serialize successfull, see http://lu.php.net/manual/en/function.unserialize.php
		        	if ($data === false && $storage[$serial] !== serialize(false)){
		        		throw new common_exception_Error("Unable to unserialize session entry identified by \"".$serial.'"');
		        	}
		        	$this->items[$serial] = $data;
		        } else {
		        	throw new tao_models_classes_cache_NotFoundException('Failed to get ('.$serial.')');
		        }
        	} else {
        		throw new tao_models_classes_cache_NotFoundException('Failed to get ('.$serial.')');
        	}
        }
        $returnValue = $this->items[$serial];
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003706 end

        return $returnValue;
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
        if (isset($this->items[$serial])) {
	        unset($this->items[$serial]);
	        unset($_SESSION[SESSION_NAMESPACE][static::SESSION_KEY][$serial]);
        }
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
        Session::removeAttribute(static::SESSION_KEY);
        $this->items = array();
        // section 127-0-1-1--18485ef3:13542665222:-8000:00000000000065B1 end
    }

    /**
     * During the destruct the variables
     * are written to the session
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function __destruct()
    {
        // section 127-0-1-1--18485ef3:13542665222:-8000:00000000000065AF begin
        foreach ($this->items as $key => $value) {
			// not clean put reading the session and then adding data to the session causses concurrency problems
			// therefore this DOES NOT WORK: session::setAttribute(static::SESSION_KEY, $storage)
        	$_SESSION[SESSION_NAMESPACE][static::SESSION_KEY][$key] = serialize($value);
    	}
        // section 127-0-1-1--18485ef3:13542665222:-8000:00000000000065AF end
    }

    /**
     * Short description of method getAll
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function getAll()
    {
        $returnValue = array();

        // section 127-0-1-1--66865e2:1353e542706:-8000:000000000000370B begin
        // unserialize missing elements
        if (Session::hasAttribute(static::SESSION_KEY)) {
    		foreach (Session::getAttribute(static::SESSION_KEY) as $serial => $raw) {
    			if (!isset($this->items[$serial])) {
    				// loads the serial to the item
    				$this->get($serial);
    			}
	        }
    	}
    	$returnValue = $this->items;
        // section 127-0-1-1--66865e2:1353e542706:-8000:000000000000370B end

        return (array) $returnValue;
    }

    /**
     * Short description of method contains
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string serial
     * @return boolean
     */
    public function contains($serial)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-5c662a7:1364f362602:-8000:00000000000038CB begin
        if (isset($this->items[$serial])) {
        	$returnValue = true;
        } elseif (!empty($serial) && Session::hasAttribute(static::SESSION_KEY)){
        	$storage = Session::getAttribute(static::SESSION_KEY);
        	$returnValue = isset($storage[$serial]);
        }
        // section 127-0-1-1-5c662a7:1364f362602:-8000:00000000000038CB end

        return (bool) $returnValue;
    }

} /* end of class tao_models_classes_cache_SessionCache */

?>