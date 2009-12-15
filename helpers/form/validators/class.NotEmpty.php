<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/validators/class.NotEmpty.php
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 19.11.2009, 15:14:40 with ArgoUML PHP module 
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
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCA-includes begin
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCA-includes end

/* user defined constants */
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCA-constants begin
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCA-constants end

/**
 * Short description of class tao_helpers_form_validators_NotEmpty
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_NotEmpty
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
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BEA begin
		
		parent::__construct($options);
		
		$this->message = __('This field must not be empty');
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BEA end
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

        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCB begin
		
		$values = $this->values;
		if(count($values) > 1){
			$returnValue = true;
		}
		else{
			$value = $this->getValue();
			if(!empty($value)){
				$returnValue = true;
			}
		}
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCB end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_NotEmpty */

?>