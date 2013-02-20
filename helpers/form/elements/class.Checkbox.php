<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/elements/class.Checkbox.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 20.02.2013, 16:31:25 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage helpers_form_elements
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_MultipleElement
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 */
require_once('tao/helpers/form/elements/class.MultipleElement.php');

/* user defined includes */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000198A-includes begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000198A-includes end

/* user defined constants */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000198A-constants begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000198A-constants end

/**
 * Short description of class tao_helpers_form_elements_Checkbox
 *
 * @abstract
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage helpers_form_elements
 */
abstract class tao_helpers_form_elements_Checkbox
    extends tao_helpers_form_elements_MultipleElement
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute widget
     *
     * @access protected
     * @var string
     */
    protected $widget = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox';

    // --- OPERATIONS ---

    /**
     * Short description of method getRawValue
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return array
     */
    public function getRawValue()
    {
        $returnValue = array();

        // section 127-0-1-1-7e796816:13cf8387df3:-8000:0000000000003C8C begin
        $returnValue = $this->values;
        // section 127-0-1-1-7e796816:13cf8387df3:-8000:0000000000003C8C end

        return (array) $returnValue;
    }

} /* end of abstract class tao_helpers_form_elements_Checkbox */

?>