<?php

error_reporting(E_ALL);

/**
 * TAO - tao/models/classes/class.SessionCache.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 03.02.2012, 11:06:37 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_models_classes_Cache
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/models/classes/interface.Cache.php');

/* user defined includes */
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036DE-includes begin
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036DE-includes end

/* user defined constants */
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036DE-constants begin
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036DE-constants end

/**
 * Short description of class tao_models_classes_SessionCache
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_SessionCache
        implements tao_models_classes_Cache
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
     * Short description of attribute instances
     *
     * @access private
     * @var array
     */
    private static $instances = array();

    /**
     * Short description of attribute SESSION_KEY
     *
     * @access public
     * @var string
     */
    const SESSION_KEY = 'cache';

    // --- OPERATIONS ---

    /**
     * Short description of method put
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Serializable item
     * @return mixed
     */
    public function put( tao_models_classes_Serializable $item)
    {
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003703 begin
    	if(!is_null($item)){
			$this->items[$item->getSerial()] = $item; 
    	}
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003703 end
    }

    /**
     * Short description of method get
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
        if (isset($this->items[$serial])) {
        	$returnValue = $this->items[$serial];
        } elseif (!empty($serial) && Session::hasAttribute(static::SESSION_KEY)){
        	$storage = Session::getAttribute(static::SESSION_KEY);
	        if(isset($storage[$serial])){

	        	$data = @unserialize($storage[$serial]);
	        
	        	if($data === false || !$data instanceof tao_models_classes_Serializable){
	        		throw new common_exception_Error("Unable to unserialize session entry identified by \"".$serial.'"');
	        	}
	        	$this->items[$serial] = $data;
	        	$returnValue = $data;
	        }
        }
        if (is_null($returnValue)) {
        	common_Logger::w('Failed to get ('.$serial.')', array('TAOITEMS', 'QTI'));
        }
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003706 end

        return $returnValue;
    }

    /**
     * Short description of method remove
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Serializable item
     * @return mixed
     */
    public function remove( tao_models_classes_Serializable $item)
    {
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003700 begin
        unset($this->items[$item->getSerial()]);
        unset($_SESSION[SESSION_NAMESPACE][static::SESSION_KEY][$item->getSerial()]);
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003700 end
    }

    /**
     * Short description of method purge
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
     * Short description of method singleton
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return tao_models_classes_SessionCache
     */
    public static function singleton()
    {
        $returnValue = null;

        // section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036E2 begin
        $cacheName = get_called_class();
        if (!isset(self::$instances[$cacheName])) {
        	self::$instances[$cacheName] = new $cacheName();
        }
        
        $returnValue = self::$instances[$cacheName];
        // section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036E2 end

        return $returnValue;
    }

    /**
     * private to prevent direct instanciation
     *
     * @access private
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    private function __construct()
    {
        // section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036EC begin
        // section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036EC end
    }

    /**
     * Short description of method __destruct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function __destruct()
    {
        // section 127-0-1-1--18485ef3:13542665222:-8000:00000000000065AF begin
        foreach ($this->items as $item) {
			// not clean put reading the session and then adding data to the session causses concurrency problems
			// therefore this DOES NOT WORK: session::setAttribute(static::SESSION_KEY, $storage)
        	$_SESSION[SESSION_NAMESPACE][static::SESSION_KEY][$item->getSerial()] = serialize($item);
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
    	if (Session::hasAttribute(static::SESSION_KEY)) {
    		foreach (Session::getAttribute(static::SESSION_KEY) as $serial => $raw) {
    			if (!isset($this->items[$serial])) {
    				$data = @unserialize($raw);
    				if ($data !== false) {
    					$this->items[$serial] = $data;
    				} else {
    					common_Logger::w('Error while unserializing ('.$serial.')');
    				}
    			}
	        }
    	}
		$returnValue = $this->items;
        // section 127-0-1-1--66865e2:1353e542706:-8000:000000000000370B end

        return (array) $returnValue;
    }

} /* end of class tao_models_classes_SessionCache */

?>