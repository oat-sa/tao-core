<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 30.09.2009, 16:56:26 with ArgoUML PHP module 
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
 * include tao_helpers_form_FormElement
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

} /* end of abstract class tao_helpers_form_elements_MultipleElement */

?>