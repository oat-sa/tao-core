<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 20.10.2009, 15:51:36 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Represents a FormElement entity
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/class.FormElement.php');

/* user defined includes */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019FE-includes begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019FE-includes end

/* user defined constants */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019FE-constants begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019FE-constants end

/**
 * Short description of class tao_helpers_form_elements_MultipleElement
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements
 */
abstract class tao_helpers_form_elements_MultipleElement
    extends tao_helpers_form_FormElement
{
    // --- ASSOCIATIONS ---


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

    // --- OPERATIONS ---

    /**
     * Short description of method setOptions
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  array options
     * @return mixed
     */
    public function setOptions($options)
    {
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A07 begin
		$this->options = $options;
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A07 end
    }

    /**
     * Short description of method getOptions
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return array
     */
    public function getOptions()
    {
        $returnValue = array();

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A37 begin
		$returnValue = $this->options;
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A37 end

        return (array) $returnValue;
    }

    /**
     * Short description of method setValue
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string value
     * @return mixed
     */
    public function setValue($value)
    {
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A2C begin
		$this->value = tao_helpers_Uri::encode($value);
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A2C end
    }

    /**
     * Short description of method addValue
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string value
     * @return mixed
     */
    public function addValue($value)
    {
        // section 127-0-1-1-bed3971:124720c750d:-8000:0000000000001A97 begin
		$this->values[] = tao_helpers_Uri::encode($value);
        // section 127-0-1-1-bed3971:124720c750d:-8000:0000000000001A97 end
    }

    /**
     * Short description of method getValues
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return array
     */
    public function getValues()
    {
        $returnValue = array();

        // section 127-0-1-1-bed3971:124720c750d:-8000:0000000000001A9D begin
		$returnValue = $this->values;
        // section 127-0-1-1-bed3971:124720c750d:-8000:0000000000001A9D end

        return (array) $returnValue;
    }

} /* end of abstract class tao_helpers_form_elements_MultipleElement */

?>