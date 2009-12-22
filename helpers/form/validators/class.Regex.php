<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/validators/class.Regex.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 22.12.2009, 16:53:44 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_Validator
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/class.Validator.php');

/* user defined includes */
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C08-includes begin
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C08-includes end

/* user defined constants */
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C08-constants begin
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C08-constants end

/**
 * Short description of class tao_helpers_form_validators_Regex
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_Regex
    extends tao_helpers_form_Validator
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
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C09 begin
		
		parent::__construct($options);
		
		if(!isset($this->options['format'])){
			throw new Exception("Please set the format options (define your regular expression)!");
		}
		$this->message = __('The format of this field is not valid.');
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C09 end
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return boolean
     */
    public function evaluate()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C13 begin
		
		$value = $this->getValue();
		if(preg_match($this->options['format'], $value)){
			 $returnValue = true;
		}
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C13 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_Regex */

?>