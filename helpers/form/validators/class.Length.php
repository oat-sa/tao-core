<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/validators/class.Length.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 19.11.2009, 15:57:09 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_validators
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_Validator
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/class.Validator.php');

/* user defined includes */
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C17-includes begin
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C17-includes end

/* user defined constants */
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C17-constants begin
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C17-constants end

/**
 * Short description of class tao_helpers_form_validators_Length
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_Length
    extends tao_helpers_form_Validator
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C18 begin
		
		parent::__construct($options);
		
		if( isset($this->options['min']) && isset($this->options['max']) ){
			$this->message = __('Invalid field length')." (minimum ".$this->options['min'].", maximum ".$this->options['max'].")";
		}
		else if( isset($this->options['min']) && !isset($this->options['max']) ){
			$this->message = __('This field is too short')." (minimum ".$this->options['min'].")";
		}
		else if( !isset($this->options['min']) && isset($this->options['max']) ){
			$this->message = __('This field is too long')." (maximum ".$this->options['max'].")";
		}
		else{
			throw new Exception("Please set 'min' and/or 'max' options!");
		}
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C18 end
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return boolean
     */
    public function evaluate()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C22 begin
		
		$value = $this->getValue();
		if( isset($this->options['min']) && isset($this->options['max']) ){
			if(strlen($value) >= $this->options['min'] && strlen($value) <= $this->options['max']){
				$returnValue = true;
			}
		}
		else if( isset($this->options['min']) && !isset($this->options['max']) ){
			if(strlen($value) >= $this->options['min']){
				$returnValue = true;
			}
		}
		else if( !isset($this->options['min']) && isset($this->options['max']) ){
			if(strlen($value) <= $this->options['max']){
				$returnValue = true;
			}
		}
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C22 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_Length */

?>