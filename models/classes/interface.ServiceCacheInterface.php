<?php

error_reporting(E_ALL);

/**
 * TAO - tao/models/classes/interface.ServiceCacheInterface.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 05.10.2011, 11:06:28 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-3a6b44f1:1326d50ba09:-8000:00000000000065CA-includes begin
// section 127-0-1-1-3a6b44f1:1326d50ba09:-8000:00000000000065CA-includes end

/* user defined constants */
// section 127-0-1-1-3a6b44f1:1326d50ba09:-8000:00000000000065CA-constants begin
// section 127-0-1-1-3a6b44f1:1326d50ba09:-8000:00000000000065CA-constants end

/**
 * Short description of class tao_models_classes_ServiceCacheInterface
 *
 * @access public
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
interface tao_models_classes_ServiceCacheInterface
{


    // --- OPERATIONS ---

    /**
     * Short description of method setCache
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string methodName
     * @param  array args
     * @param  array value
     * @return boolean
     */
    public function setCache($methodName, $args = array(), $value = array());

    /**
     * Short description of method getCache
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string methodName
     * @param  array args
     * @return mixed
     */
    public function getCache($methodName, $args = array());

    /**
     * Short description of method clearCache
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string methodName
     * @param  array args
     * @return boolean
     */
    public function clearCache($methodName = '', $args = array());

} /* end of interface tao_models_classes_ServiceCacheInterface */

?>