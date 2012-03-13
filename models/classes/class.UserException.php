<?php

error_reporting(E_ALL);

/**
 * TAO - tao/models/classes/class.UserException.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 13.03.2012, 11:53:25 with ArgoUML PHP module 
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
 * include common_Exception
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('common/class.Exception.php');

/* user defined includes */
// section 127-0-1-1-235ad9e5:12db7a01f69:-8000:0000000000002D13-includes begin
// section 127-0-1-1-235ad9e5:12db7a01f69:-8000:0000000000002D13-includes end

/* user defined constants */
// section 127-0-1-1-235ad9e5:12db7a01f69:-8000:0000000000002D13-constants begin
// section 127-0-1-1-235ad9e5:12db7a01f69:-8000:0000000000002D13-constants end

/**
 * Short description of class tao_models_classes_UserException
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_UserException
    extends common_Exception
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string message
     * @return mixed
     */
    public function __construct($message)
    {
        // section 127-0-1-1-235ad9e5:12db7a01f69:-8000:0000000000002D14 begin
        
    	parent::__construct($message);
    	
        // section 127-0-1-1-235ad9e5:12db7a01f69:-8000:0000000000002D14 end
    }

} /* end of class tao_models_classes_UserException */

?>