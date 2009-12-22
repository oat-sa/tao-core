<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/validators/class.Callback.php
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
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D58-includes begin
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D58-includes end

/* user defined constants */
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D58-constants begin
// section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D58-constants end

/**
 * Short description of class tao_helpers_form_validators_Callback
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D69 begin
		
		parent::__construct($options);
		
		$this->message = $this->options['message'];
		
		if(!isset($this->options['function']) && !isset($this->options['class']) && !isset($this->options['method'])){
			throw new Exception("Please define a callback function or method");
		}
		
        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D69 end
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

        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D73 begin
		
		if(isset($this->options['function'])){
			$callback = $this->options['function'];
			if(function_exists($callback)){
				$returnValue = (bool)$callback($this->getValue());
			}
		}
		else{
			$class = $this->options['class'];
			$method = $this->options['method'];
			if(class_exists($class)){
				$callback = new $class();
				if(method_exists($callback, $method)){
					$returnValue = (bool)$callback->$method($this->getValue());
				}
			}
		}
		
        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D73 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_Callback */

?>