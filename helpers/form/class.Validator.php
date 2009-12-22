<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/class.Validator.php
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
 * @subpackage helpers_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Represents a FormElement entity
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/class.FormElement.php');

/* user defined includes */
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001B73-includes begin
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001B73-includes end

/* user defined constants */
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001B73-constants begin
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001B73-constants end

/**
 * Short description of class tao_helpers_form_Validator
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form
 */
abstract class tao_helpers_form_Validator
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd :     // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    /**
     * Short description of attribute options
     *
     * @access protected
     * @var array
     */
    protected $options = array();

    /**
     * Short description of attribute values
     *
     * @access protected
     * @var array
     */
    protected $values = array();

    /**
     * Short description of attribute message
     *
     * @access protected
     * @var string
     */
    protected $message = '';

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
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001B8C begin
		
		$this->options = $options;
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001B8C end
    }

    /**
     * Short description of method getName
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function getName()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCE begin
		
		$returnValue = str_replace('tao_helpers_form_validators_', '', get_class($this));
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCE end

        return (string) $returnValue;
    }

    /**
     * Short description of method setValue
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string value
     * @return mixed
     */
    public function setValue($value)
    {
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001B9A begin
		
		$this->values[] = $value;
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001B9A end
    }

    /**
     * Short description of method getValue
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function getValue()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BD0 begin
		
		if(count($this->values) == 1){
			 $returnValue = $this->values[0];
		}
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BD0 end

        return (string) $returnValue;
    }

    /**
     * Short description of method setValues
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array values
     * @return mixed
     */
    public function setValues($values)
    {
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001B9D begin
		
		$this->values = $values;
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001B9D end
    }

    /**
     * Short description of method getMessage
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function getMessage()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BDD begin
		
		$returnValue = $this->message;
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BDD end

        return (string) $returnValue;
    }

    /**
     * Short description of method evaluate
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return boolean
     */
    public abstract function evaluate();

} /* end of abstract class tao_helpers_form_Validator */

?>