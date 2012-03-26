<?php

error_reporting(E_ALL);

/**
 * TAO - tao/models/classes/cache/class.Proxy.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 26.03.2012, 17:31:32 with ArgoUML PHP module 
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
// section 127-0-1-1-5c662a7:1364f362602:-8000:00000000000038E9-includes begin
// section 127-0-1-1-5c662a7:1364f362602:-8000:00000000000038E9-includes end

/* user defined constants */
// section 127-0-1-1-5c662a7:1364f362602:-8000:00000000000038E9-constants begin
// section 127-0-1-1-5c662a7:1364f362602:-8000:00000000000038E9-constants end

/**
 * Short description of class tao_models_classes_cache_Proxy
 *
 * @abstract
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes_cache
 */
abstract class tao_models_classes_cache_Proxy
    extends tao_models_classes_Service
        implements tao_models_classes_cache_Cache
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute implementation
     *
     * @access private
     * @var Cache
     */
    private $implementation = null;

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
        $this->implementation->put($mixed, $serial);
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
        $returnValue = $this->implementation->get($serial);
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003706 end

        return $returnValue;
    }

    /**
     * removes an entry from the cache
     * throws an exception iif not found
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string serial
     * @return mixed
     */
    public function remove($serial)
    {
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003700 begin
        $this->implementation->remove($serial);
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
        $this->implementation->purge();
        // section 127-0-1-1--18485ef3:13542665222:-8000:00000000000065B1 end
    }

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function __construct()
    {
        // section 127-0-1-1-5c662a7:1364f362602:-8000:00000000000038F0 begin
        $this->implementation = $this->getImplementation();
        // section 127-0-1-1-5c662a7:1364f362602:-8000:00000000000038F0 end
    }

    /**
     * Short description of method getImplementation
     *
     * @abstract
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return tao_models_classes_cache_Cache
     */
    public abstract function getImplementation();

} /* end of abstract class tao_models_classes_cache_Proxy */

?>