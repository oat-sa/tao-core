<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/xhtml/template/class.Form.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 19.12.2011, 14:51:55 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers_form_xhtml_template
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_xhtml_Form
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/helpers/form/xhtml/class.Form.php');

/* user defined includes */
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004EF7-includes begin
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004EF7-includes end

/* user defined constants */
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004EF7-constants begin
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004EF7-constants end

/**
 * Short description of class tao_helpers_form_xhtml_template_Form
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers_form_xhtml_template
 */
class tao_helpers_form_xhtml_template_Form
    extends tao_helpers_form_xhtml_Form
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getValues
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string groupName
     * @return array
     */
    public function getValues($groupName = '')
    {
        $returnValue = array();

        // section 127-0-1-1-34f10d1c:12e0a0f28d8:-8000:0000000000004F7C begin
        
        $returnValue = parent::getValues($groupName);
        
        foreach($this->elements as $element){
        	if($element instanceof tao_helpers_form_elements_Template){
        		$returnValue[tao_helpers_Uri::decode($element->getName())] = $element->getValues();
        	}
        }
        
        // section 127-0-1-1-34f10d1c:12e0a0f28d8:-8000:0000000000004F7C end

        return (array) $returnValue;
    }

} /* end of class tao_helpers_form_xhtml_template_Form */

?>