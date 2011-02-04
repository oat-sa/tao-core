<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/validators/class.AlphaNum.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 03.02.2011, 16:09:31 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_validators_Regex
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/validators/class.Regex.php');

/* user defined includes */
// section 127-0-1-1-652edf41:12dec09453b:-8000:0000000000004EDE-includes begin
// section 127-0-1-1-652edf41:12dec09453b:-8000:0000000000004EDE-includes end

/* user defined constants */
// section 127-0-1-1-652edf41:12dec09453b:-8000:0000000000004EDE-constants begin
// section 127-0-1-1-652edf41:12dec09453b:-8000:0000000000004EDE-constants end

/**
 * Short description of class tao_helpers_form_validators_AlphaNum
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_AlphaNum
    extends tao_helpers_form_validators_Regex
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1-652edf41:12dec09453b:-8000:0000000000004EE0 begin
        
    	if(isset($options['format'])){
    		unset($options['format']);	//the pattern cannot be overriden
    	}
    	
    	if(isset($options['allow_punctuation'])){
    		$pattern = "/^[a-zA-Z0-9_\-]*$/";
    	}
    	else{
    		$pattern = "/^[a-zA-Z0-9]*$/";
    	}
    	
    	parent::__construct(array_merge(array('format' => $pattern), $options));
    	
        // section 127-0-1-1-652edf41:12dec09453b:-8000:0000000000004EE0 end
    }

} /* end of class tao_helpers_form_validators_AlphaNum */

?>