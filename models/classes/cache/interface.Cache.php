<?php

error_reporting(E_ALL);

/**
 * basic interface a cache implementation has to implement
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
 * basic interface a cache implementation has to implement
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
     * test whenever an entry associted to the serial exists
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string serial
     * @return boolean
     */
    public function has($serial);

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