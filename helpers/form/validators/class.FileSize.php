<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/validators/class.FileSize.php
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
// section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CDE-includes begin
// section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CDE-includes end

/* user defined constants */
// section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CDE-constants begin
// section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CDE-constants end

/**
 * Short description of class tao_helpers_form_validators_FileSize
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_FileSize
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
    public function __construct($options)
    {
        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CDF begin

		parent::__construct($options);
		
		if( isset($this->options['min']) && isset($this->options['max']) ){
			$this->message = __('Invalid file size')." (minimum ".$this->options['min']." octets, maximum ".$this->options['max']." octets)";
		}
		else if( !isset($this->options['min']) && isset($this->options['max']) ){
			$this->options['min'] = 0;
			$this->message = __('The uploaded file is too large')." (maximum ".$this->options['max']." octets)";
		}
		else{
			throw new Exception("Please set 'min' and/or 'max' options!");
		}
		
        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CDF end
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

        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CE3 begin
		
		$value = $this->values[0];
		if(is_array($value)){
			if(isset($value['size'])){
				if($value['size'] >= $this->options['min'] && $value['size'] <= $this->options['max']){
					$returnValue = true;
				}
			}
		}
		
        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CE3 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_FileSize */

?>