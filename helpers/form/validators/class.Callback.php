<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/validators/class.Callback.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 22.12.2011, 14:51:41 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
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
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/helpers/form/class.Validator.php');

/* user defined includes */
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D58-includes begin
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D58-includes end

/* user defined constants */
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D58-constants begin
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D58-constants end

/**
 * Short description of class tao_helpers_form_validators_Callback
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_Callback
    extends tao_helpers_form_Validator
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D69 begin
		
		parent::__construct($options);
		
		if(isset($this->options['message'])) {
			$this->message = $this->options['message'];
		}
		
		if(!isset($this->options['function']) 
			&& !((isset($this->options['class']) || isset($this->options['object'])) 
				&& isset($this->options['method']))
			){
			throw new Exception("Please define a callback function or method");
		}
		
        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D69 end
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  values
     * @return boolean
     */
    public function evaluate($values)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D73 begin
		
		if(isset($this->options['function'])){
			$callback = $this->options['function'];
			if(function_exists($callback)){
				$returnValue = (bool)$callback($values);
			} else {
				throw new common_Exception("callback function does not exist");
			}
		}
		else if(isset($this->options['class'])){
			$class = $this->options['class'];
			$method = $this->options['method'];
			if(class_exists($class)){
				$callback = new $class();
				if(method_exists($callback, $method)){
					$returnValue = (bool)$callback->$method($values);
				} else {
					throw new common_Exception("callback methode does not exist");
				}
			}
		}
		else if(isset($this->options['object'])){
			$object = $this->options['object'];
			$method = $this->options['method'];
			if(method_exists($object, $method)){
				$returnValue = (bool)$object->$method($values);
			} else {
				throw new common_Exception("callback methode does not exist");
			}
		}
		
        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D73 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_Callback */

?>