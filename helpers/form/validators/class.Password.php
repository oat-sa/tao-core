<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/validators/class.Password.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 22.12.2009, 16:53:45 with ArgoUML PHP module 
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
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D57-includes begin
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D57-includes end

/* user defined constants */
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D57-constants begin
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D57-constants end

/**
 * Short description of class tao_helpers_form_validators_Password
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_Password
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
        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D5C begin
		
		parent::__construct($options);
		
		$this->message = __('Password are not matching');
		
		$elementSet = true;
		if(!isset($this->options['password2_ref'])){
			$elementSet = false;
		}
		else{
			if(is_null($this->options['password2_ref']) || !($this->options['password2_ref'] instanceof tao_helpers_form_FormElement)){
				$elementSet = false;
			}
		}
		
		if(!$elementSet){
			throw new Exception("Please set the reference of the second password element");
		}
        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D5C end
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

        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D66 begin
		
		$secondElement = $this->options['password2_ref'];
		if($this->getValue() == $secondElement->getValue() && trim($this->getValue()) != ''){
			$returnValue = true;
		}
		
        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D66 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_Password */

?>