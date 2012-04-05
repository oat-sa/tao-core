<?php

error_reporting(E_ALL);

/**
 * TAO - tao/models/classes/cache/interface.Cache.php
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

/* user defined includes */
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036F2-includes begin
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036F2-includes end

/* user defined constants */
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036F2-constants begin
// section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036F2-constants end

/**
 * Short description of class tao_models_classes_cache_Cache
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes_cache
 */
interface tao_models_classes_cache_Cache
{


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
    public function put($mixed, $serial = null);

    /**
     * gets the entry associted to the serial
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string serial
     * @return tao_models_classes_Serializable
     */
    public function get($serial);

    /**
     * removes an entry from the cache
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string serial
     * @return mixed
     */
    public function remove($serial);

    /**
     * empties the cache
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function purge();

} /* end of interface tao_models_classes_cache_Cache */

?>