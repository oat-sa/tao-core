<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/validators/class.Integer.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 30.03.2010, 16:49:38 with ArgoUML PHP module 
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
 * The validators enable you to perform a validation callback on a form element.
 * It's provide a model of validation and must be overriden.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/class.Validator.php');

/* user defined includes */
// section 127-0-1-1-d42bee:127af842275:-8000:0000000000002380-includes begin
// section 127-0-1-1-d42bee:127af842275:-8000:0000000000002380-includes end

/* user defined constants */
// section 127-0-1-1-d42bee:127af842275:-8000:0000000000002380-constants begin
// section 127-0-1-1-d42bee:127af842275:-8000:0000000000002380-constants end

/**
 * Short description of class tao_helpers_form_validators_Integer
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_Integer
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
        // section 127-0-1-1-d42bee:127af842275:-8000:0000000000002382 begin
        
   		parent::__construct($options);
		
        // section 127-0-1-1-d42bee:127af842275:-8000:0000000000002382 end
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

        // section 127-0-1-1-d42bee:127af842275:-8000:0000000000002386 begin
        
		$rowValue = $this->getValue();
        $value = intval($rowValue);
		if(empty($rowValue)){
			$returnValue = true;//no need to go further. To check if not empty, use the NotEmpty validator
			return $returnValue;
		}
        if(!is_numeric($rowValue) || $value != $rowValue){
        	$this->message = __('The value of this field must be an integer');
        }
        else{
        	if(isset($this->options['min']) || isset($this->options['max'])){
        		$this->message = __('Invalid field range');
        		
	        	if(isset($this->options['min']) && isset($this->options['max'])){
	        		
					$this->message .= ' (' . __('minimum value: ').$this->options['min'] . ', ' .  __('maximum value: ').$this->options['max'].')';
	        		
					if($this->options['min'] <=  $value && $value <= $this->options['max']){
						$returnValue = true;
					}
	        	}else if(isset($this->options['min']) && !isset($this->options['max'])){

        			$this->message .= ' (' . __('minimum value: ').$this->options['min'] .')';
	        		
					if($this->options['min'] <=  $value){
						$returnValue = true;
					}
	        	}else if(!isset($this->options['min']) && isset($this->options['max'])){
					
        			$this->message .= ' (' . __('maximum value: ').$this->options['max'].')';
	        		
					if($value <= $this->options['max']){
						$returnValue = true;
					}
	        	}
        	}
        	else{
        		$returnValue = true;
        	}
        }
		
        // section 127-0-1-1-d42bee:127af842275:-8000:0000000000002386 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_Integer */

?>